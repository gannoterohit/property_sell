<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSupportController extends BaseApiController
{
    public function complaintOptions(Request $request) { $this->allow($request,'support.view'); return $this->sendSuccess(['categories'=>Complaint::CATEGORIES,'statuses'=>Complaint::STATUSES,'resolution_categories'=>Complaint::RESOLUTION_CATEGORIES,'priorities'=>['low','medium','high','urgent'],'assignees'=>User::where('role','admin')->where('is_blocked',false)->get(['id','name'])]); }

    public function complaints(Request $request)
    {
        $this->allow($request,'support.view');
        $q=Complaint::with(['user:id,name,email,role','room:id,title,slug','assignee:id,name']);
        if($request->status==='open')$q->whereNotIn('status',['resolved','rejected','closed']); elseif($request->filled('status'))$q->where('status',$request->status);
        foreach(['category','priority','assigned_to'] as $field) if($request->filled($field))$q->where($field,$request->$field);
        if($request->sla==='overdue')$q->whereNotIn('status',['resolved','rejected','closed'])->where('due_at','<',now());
        if($request->sla==='escalated')$q->whereNotNull('escalated_at')->whereNotIn('status',['resolved','rejected','closed']);
        if($request->filled('search')){$s=$request->search;$q->where(fn($x)=>$x->where('ticket_number','like',"%$s%")->orWhere('subject','like',"%$s%"));}
        return $this->sendSuccess(['stats'=>['open'=>Complaint::whereNotIn('status',['resolved','rejected','closed'])->count(),'overdue'=>Complaint::whereNotIn('status',['resolved','rejected','closed'])->where('due_at','<',now())->count(),'escalated'=>Complaint::whereNotNull('escalated_at')->whereNotIn('status',['resolved','rejected','closed'])->count(),'resolved'=>Complaint::where('status','resolved')->count()],'tickets'=>$q->latest()->paginate($this->limit($request))]);
    }

    public function complaintShow(Request $request, Complaint $complaint){$this->allow($request,'support.view');return $this->sendSuccess($complaint->load(['user','againstUser','room','assignee','replies.user','activities.actor']));}

    public function complaintUpdate(Request $request, Complaint $complaint)
    {
        $this->allow($request,'support.manage');$data=$request->validate(['status'=>['required',Rule::in(array_keys(Complaint::STATUSES))],'priority'=>['required',Rule::in(['low','medium','high','urgent'])],'assigned_to'=>['nullable',Rule::exists('users','id')->where('role','admin')],'resolution'=>'nullable|string|max:10000','resolution_category'=>['nullable',Rule::in(array_keys(Complaint::RESOLUTION_CATEGORIES))],'due_at'=>'nullable|date','escalated'=>'nullable|boolean']);
        $old=$complaint->status;$data['closed_at']=in_array($data['status'],['resolved','rejected','closed'],true)?now():null;$data['escalated_at']=$request->boolean('escalated')?($complaint->escalated_at?:now()):null;unset($data['escalated']);$complaint->update($data);
        $complaint->activities()->create(['actor_id'=>$request->user()->id,'type'=>'status','status_from'=>$old,'status_to'=>$complaint->status,'description'=>'Ticket management details updated.']);
        \App\Services\NotificationService::notifyComplaintUpdated(
            $complaint->user_id,
            $complaint->ticket_number,
            $complaint->status,
            route('complaints.show', $complaint)
        );
        return $this->sendSuccess($complaint->fresh(),'Complaint updated successfully');
    }

    public function complaintReply(Request $request, Complaint $complaint)
    {
        $this->allow($request,'support.manage');$data=$request->validate(['message'=>'required|string|min:2|max:5000','attachment'=>'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120','is_internal'=>'nullable|boolean']);
        if($request->hasFile('attachment'))$data['attachment_path']=$request->file('attachment')->store('complaints/replies','local');unset($data['attachment']);$data['user_id']=$request->user()->id;$data['is_internal']=$request->boolean('is_internal');$reply=$complaint->replies()->create($data);
        $complaint->activities()->create(['actor_id'=>$request->user()->id,'type'=>$data['is_internal']?'note':'reply','description'=>$data['is_internal']?'Internal investigation note added.':'Support replied to the reporter.','is_internal'=>$data['is_internal']]);
        if(!$data['is_internal']&&$complaint->status==='submitted')$complaint->update(['status'=>'under_review','assigned_to'=>$complaint->assigned_to?:$request->user()->id]);return $this->sendSuccess($reply->load('user'),'Reply added successfully',201);
    }

    public function complaintReopen(Request $request, Complaint $complaint){$this->allow($request,'support.manage');if(!in_array($complaint->status,['resolved','rejected','closed'],true))return $this->sendError('Only closed complaints can be reopened.',[],422);$old=$complaint->status;$complaint->update(['status'=>'under_review','closed_at'=>null,'reopened_at'=>now(),'due_at'=>now()->addHours(24)]);$complaint->activities()->create(['actor_id'=>$request->user()->id,'type'=>'status','status_from'=>$old,'status_to'=>'under_review','description'=>'Complaint reopened for further investigation.']);return $this->sendSuccess($complaint,'Complaint reopened successfully');}
    public function contactMessages(Request $request){$this->allow($request,'support.view');$q=ContactMessage::query();if($request->filled('is_read'))$q->where('is_read',$request->boolean('is_read'));if($request->filled('search')){$s=$request->search;$q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%")->orWhere('subject','like',"%$s%"));}return $this->sendSuccess($q->latest()->paginate($this->limit($request)));}
    public function markContactRead(Request $request, ContactMessage $message){$this->allow($request,'support.manage');$message->update(['is_read'=>true]);return $this->sendSuccess($message,'Message marked as read');}
    public function deleteContact(Request $request, ContactMessage $message){$this->allow($request,'support.manage');$message->delete();return $this->sendSuccess([],'Contact message deleted');}
    private function allow(Request $request,string $permission):void{abort_unless($request->user()->hasAdminPermission($permission),403,'You do not have permission for this admin operation.');}
    private function limit(Request $request):int{return max(1,min(50,$request->integer('limit',20)));}
}
