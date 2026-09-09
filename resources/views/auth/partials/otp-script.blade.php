<script>
document.addEventListener('DOMContentLoaded', () => {
    const mode = @json($mode);
    const status = document.getElementById('status-message');
    const email = document.getElementById('email');
    const otp = document.getElementById('otp');

    const show = (message, type = 'error') => {
        status.className = 'auth-alert ' + type;
        status.classList.remove('hidden');
        const icon = status.querySelector('i');
        if (icon) {
            icon.className = 'fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
        }
        const span = status.querySelector('span');
        if (span) {
            span.innerHTML = message;
        }
    };

    const loading = (button, on) => {
        if (on) {
            button.dataset.label = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Please wait...</span>';
        } else {
            button.disabled = false;
            if (button.dataset.label) {
                button.innerHTML = button.dataset.label;
            }
        }
    };

    const post = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });
        let data = {};
        try {
            data = await response.json();
        } catch (e) {}
        if (!response.ok && !data.message) {
            data.message = 'The request could not be completed.';
        }
        return data;
    };

    const send = async (button) => {
        const address = email.value.trim();
        if (!/^\S+@\S+\.\S+$/.test(address)) {
            show('Enter a valid email address.');
            email.focus();
            return false;
        }

        loading(button, true);
        try {
            const data = await post(@json(route('send.otp')), {
                email: address,
                identifier: address,
                context: mode
            });

            if (!data.success) {
                if (data.already_registered) {
                    show(`${data.message} <a href="{{ route('login') }}" style="text-decoration:underline;font-weight:700;color:inherit;margin-left:6px;">Login here &rarr;</a>`);
                } else {
                    show(data.message || 'We could not send the code. Please try again.');
                }
                return false;
            }

            document.getElementById('email-display').textContent = address;
            document.getElementById(mode === 'login' ? 'email-step' : 'details-step').classList.add('hidden');
            document.getElementById('otp-step').classList.remove('hidden');
            otp.value = '';
            otp.focus();
            show('Verification code sent. Please check your inbox.', 'success');
            return true;
        } catch (e) {
            show('Connection error. Please check your internet and try again.');
            return false;
        } finally {
            loading(button, false);
        }
    };

    const submitDetailsForm = document.getElementById(mode === 'login' ? 'email-form' : 'details-form');
    if (submitDetailsForm) {
        submitDetailsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (mode === 'register') {
                const name = document.getElementById('name');
                if (!name || name.value.trim().length < 2) {
                    show('Enter your full name.');
                    if (name) name.focus();
                    return;
                }
                const terms = document.getElementById('terms_checkbox');
                if (terms && !terms.checked) {
                    show('Please accept the Terms and Privacy Policy.');
                    return;
                }
            }
            await send(document.getElementById('send-otp-btn'));
        });
    }

    const otpForm = document.getElementById('otp-form');
    if (otpForm) {
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = otp.value.replace(/\D/g, '');
            if (code.length !== 6) {
                show('Enter the complete 6-digit verification code.');
                otp.focus();
                return;
            }

            const button = document.getElementById('verify-otp-btn');
            loading(button, true);

            const payload = {
                email: email.value.trim(),
                otp: code
            };

            if (mode === 'register') {
                const roleInput = document.querySelector('[name="role"]:checked');
                const role = roleInput ? roleInput.value : 'user';
                const nameInput = document.getElementById('name');
                const phoneInput = document.getElementById('phone');
                const refInput = document.getElementById('referral_code_input');

                Object.assign(payload, {
                    name: nameInput ? nameInput.value.trim() : '',
                    phone: phoneInput ? phoneInput.value.trim() : '',
                    role: role,
                    referral_code: refInput ? refInput.value.trim() : ''
                });

                if (role === 'broker') {
                    const agencyName = document.getElementById('agency_name');
                    const agencyAddr = document.getElementById('agency_address');
                    const brokerLicense = document.getElementById('broker_license');
                    const agencyGst = document.getElementById('agency_gst');
                    if (agencyName) payload.agency_name = agencyName.value.trim();
                    if (agencyAddr) payload.agency_address = agencyAddr.value.trim();
                    if (brokerLicense) payload.broker_license = brokerLicense.value.trim();
                    if (agencyGst) payload.agency_gst = agencyGst.value.trim();
                }
            }

            try {
                const endpoint = (mode === 'login') ? @json(route('verify.login.otp')) : @json(route('verify.registration.otp'));
                const data = await post(endpoint, payload);

                if (data.success) {
                    show(mode === 'login' ? 'Login successful. Redirecting...' : 'Account created. Redirecting...', 'success');
                    setTimeout(() => {
                        location.href = data.redirect || @json(route('dashboard'));
                    }, 500);
                    return;
                }

                if (data.already_registered) {
                    show(`${data.message} <a href="{{ route('login') }}" style="text-decoration:underline;font-weight:700;color:inherit;margin-left:6px;">Login here &rarr;</a>`);
                } else {
                    const errMsg = (data.errors ? Object.values(data.errors).flat()[0] : null) || data.message || 'The verification code is invalid or expired.';
                    show(errMsg);
                }
            } catch (e) {
                show('Verification failed. Please try again.');
            } finally {
                loading(button, false);
            }
        });
    }

    const resendBtn = document.getElementById('resend-otp-btn');
    if (resendBtn) {
        resendBtn.addEventListener('click', (e) => send(e.currentTarget));
    }

    const backBtn = document.getElementById(mode === 'login' ? 'back-to-email-btn' : 'back-to-details-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            document.getElementById('otp-step').classList.add('hidden');
            document.getElementById(mode === 'login' ? 'email-step' : 'details-step').classList.remove('hidden');
            status.classList.add('hidden');
            email.focus();
        });
    }

    if (otp) {
        otp.addEventListener('input', () => {
            otp.value = otp.value.replace(/\D/g, '').slice(0, 6);
        });
    }
});
</script>
