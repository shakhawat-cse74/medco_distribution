<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{gen_setting()?->site_title ?? ''}}</title>
    <!-- Open Graph / Social Media Link Preview -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ gen_setting()->site_title ?? 'BanglaSoft' }}">
    <meta property="og:description" content="BanglaSoft POS & Inventory Management System">
    <meta property="og:image" content="{{ asset('logo/' . (gen_setting()->site_logo ?? 'banglasoft_logo.png')) }}">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="315">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ gen_setting()->site_title ?? 'BanglaSoft' }}">
    <meta name="twitter:image" content="{{ asset('logo/' . (gen_setting()->site_logo ?? 'banglasoft_logo.png')) }}">
    @if(!config('database.connections.saleprosaas_landlord'))
    <link rel="icon" type="image/png" href="{{url('logo', gen_setting()->site_logo)}}" />
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    @else
    <link rel="icon" type="image/png" href="{{url('../../logo', gen_setting()->site_logo)}}" />
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    @endif

    <!-- Google fonts -->
    @if(gen_setting()->font_css ?? '')
      {!! gen_setting()->font_css ?? '' !!}
    @else
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    @endif

    <!-- Custom CSS from general settings -->
    {!! gen_setting()->auth_css ?? '' !!}

    <style>
        body { font-size: 14px;font-family: 'Inter', sans-serif;}
        .vh-100 { min-height: 100vh; }
        a{color: #7c5cc4;}
        
        /* Left Side Styles */
        .login-container { padding: 3% 0; }
        .login-container form { max-width: 400px; margin: auto; }
        .form-control { height: 38px; border-radius: .25rem; border: 1px solid #ddd; }
        .btn-primary { background-color: #7c5cc4; border: none; height: 40px; border-radius: .25rem; font-weight: 600; }
        .btn-primary:hover { background-color: #6a4bb3; }
        .btn-outline-light { border: 1px solid #ddd; color: #333; height: 38px; border-radius: .25rem; font-weight: 500; }
        .btn-outline-light img { width: 20px; margin-right: 8px; }
        
        /* Right Side Styles */
        .promo-side {
            background-image: url('{{ !config('database.connections.saleprosaas_landlord') ? asset('css/promo-bg.svg') : asset('../../css/promo-bg.svg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 10% 8%;
            border-radius: 24px;
            margin: 15px;
            overflow: hidden;
            z-index: 1; 
            position: relative;
        }
        .promo-side > div {
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .dashboard-preview {
            margin-top: 38px;
            box-shadow: 0 20px 38px rgba(0,0,0,0.2);
            border-radius: 12px;
            width: 120%; /* Creates the "peek" effect */
        }
        .footer-text { font-size: 0.85rem; color: #888; }

        /* Dark Mode Variables */
        :root {
            --bg-dark: #0f172a;           /* Deep Navy background */
            --card-dark: #1e293b;         /* Slightly lighter slate for inputs/cards */
            --text-main: #f8fafc;         /* Off-white text */
            --text-muted: #94a3b8;        /* Slate gray for secondary text */
            --input-border: #334155;      /* Border for dark inputs */
        }

        body.dark-mode {
            background-color: var(--bg-dark);
            color: var(--text-main);
        }

        /* Left Side Adjustments */
        .dark-mode .form-control {
            background-color: var(--card-dark);
            border-color: var(--input-border);
            color: var(--text-main);
        }

        .dark-mode .form-control:focus {
            background-color: var(--card-dark);
            color: #fff;
            border-color: #7c5cc4;
        }

        .dark-mode .input-group-text {
            background-color: var(--card-dark) !important;
            border-color: var(--input-border) !important;
            color: var(--text-muted);
        }

        .dark-mode .text-muted {
            color: var(--text-muted) !important;
        }

        .dark-mode .btn-outline-light {
            border-color: var(--input-border);
            color: var(--text-main);
        }

        .dark-mode .btn-outline-light:hover {
            background-color: var(--input-border);
        }

        /* Horizontal Rule with "Or Login With" */
        .dark-mode hr {
            border-top: 1px solid var(--input-border);
        }

        .dark-mode .bg-white {
            background-color: var(--bg-dark) !important; /* Matches body bg */
        }

        .dark-mode .promo-side {
            opacity:0.9;
        }

        /* Footer Link Adjustments */
        .dark-mode .footer-text a {
            color: var(--text-muted) !important;
        }
        
        button.dropdown-item {display:flex;}
        
        button svg {margin:0 10px; width:20px}
    </style>
</head>
<body class="">

<div class="container-fluid">
    <div class="row vh-100">
        <div class="col-md-8 col-lg-6 mx-auto d-flex align-items-center position-relative">
            <div class="login-container w-100">
                <div class="mb-5" style="margin: auto; text-align: center;">
                    @if(gen_setting()->site_logo)
                    <img src="{{url('logo', gen_setting()->site_logo)}}" width="120">
                    @else
                    <span>{{gen_setting()->site_title}}</span>
                    @endif
                </div>

                @if(session()->has('delete_message'))
                <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('delete_message') }}</div>
                @endif
                @if(session()->has('message'))
                  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
                @endif
                @if(session()->has('not_permitted'))
                  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
                @endif
                <form method="POST" action="{{ route('login') }}" id="login-form">
                @csrf
                    <div class="form-group">
                        <label class="font-weight-600">{{__('db.UserName')}}</label>
                        <input type="name" name="name" class="form-control" placeholder="{{__('db.UserName')}}" @if(!config('app.user_verified')) value="admin" @endif required>
                        @if(session()->has('error'))
                            <p>
                                <strong>{{ session()->get('error') }}</strong>
                            </p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600">{{__('db.Password')}}</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="••••••••" @if(!config('app.user_verified')) value="admin" @endif  required>
                            <div class="input-group-append">
                                <span id="togglePassword" class="input-group-text bg-white border-left-0" style="cursor: pointer;">
                                    <svg id="icon-hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>

                                    <svg id="icon-show" class="d-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        @if(session()->has('error'))
                            <p>
                                <strong>{{ session()->get('error') }}</strong>
                            </p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        @if ((int) (gen_setting()?->disable_forgot_password ?? 0) === 0)
                        <a href="{{ route('password.request') }}" class="small font-weight-bold">{{__('db.Forgot Password?')}}</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-block shadow-sm mb-2">{{__('db.LogIn')}}</button>

                    @if(gen_setting()->disable_signup ?? 0) == 0)
                    <p class="text-center mt-5 text-muted">
                        {{__('db.Do not have an account?')}} <a href="{{url('register')}}" class="font-weight-bold">{{__('db.Register')}}</a>
                    </p>
                    @endif
                </form>

                <div class="footer-text w-100 d-flex justify-content-center mt-5">
                    <p>{{__('db.Developed By')}} <span class="external">{{gen_setting()->developed_by}}</span></p>
                </div>
            </div>
        </div>


    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. Landlord Logic
        @if(config('database.connections.saleprosaas_landlord'))
            const storedMessage = localStorage.getItem("message");
            if(storedMessage) {
                if (typeof showToast === 'function') {
                    showToast('success', storedMessage);
                }
                localStorage.removeItem("message");
            }

            const numberOfUserAccount = @json($numberOfUserAccount);
            
            // Replaces $.ajax
            fetch('{{route("package.fetchData", gen_setting()->package_id)}}')
                .then(response => response.json())
                .then(data => {
                    if(data['number_of_user_account'] > 0 && data['number_of_user_account'] <= numberOfUserAccount) {
                        const registerSection = document.querySelector(".register-section");
                        if(registerSection) registerSection.classList.add('d-none');
                    }
                })
                .catch(error => console.error('Error fetching package data:', error));
        @endif

        // 2. Alert slideUp Replacement
        const alerts = document.querySelectorAll("div.alert");
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = "all 0.8s ease";
                alert.style.opacity = "0";
                alert.style.height = "0";
                alert.style.padding = "0";
                alert.style.margin = "0";
                setTimeout(() => alert.remove(), 800);
            }, 4000);
        });

        // 3. Password Toggle Logic
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const passwordField = document.querySelector("input[name='password']");
                const iconHidden = document.getElementById("icon-hidden");
                const iconShow = document.getElementById("icon-show");

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    iconHidden.classList.add('d-none');
                    iconShow.classList.remove('d-none');
                } else {
                    passwordField.type = "password";
                    iconHidden.classList.remove('d-none');
                    iconShow.classList.add('d-none');
                }
            });
        }

        // 4. Theme Logic
        const theme = @json($theme);
        const body = document.body;
        const themeIcon = document.querySelector('#switch-theme i');

        if(theme === 'dark') {
            body.classList.add('dark-mode');
            if(themeIcon) themeIcon.classList.add('ti ti-brightness-down');
        } else {
            body.classList.remove('dark-mode');
            if(themeIcon) themeIcon.classList.add('ti ti-brightness-up');
        }

        // 5. Cookie Helper
        function setEnvCookie(cookieValue) {
            const date = new Date();
            date.setTime(date.getTime() + (1 * 24 * 60 * 60 * 1000)); // 1 day
            document.cookie = `env_name=${cookieValue}; expires=${date.toUTCString()}; path=/`;
        }

        // 6. Auto-trigger from /demo/{type} direct URL
        // যখন কেউ সরাসরি /demo/pos লিঙ্ক দিয়ে আসে, তখন index.php
        // ?demo=1&env=...&page=... সহ এই login page এ redirect করে।
        // এই block সেটা detect করে automatically login submit করে।
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('demo') === '1') {
            const env  = urlParams.get('env');
            const page = urlParams.get('page');

            if (env && page) {
                if (env === '.env.ecom' && page === 'ecom_front') {
                    // ecom frontend — নতুন tab এ খোলো, login দরকার নেই
                    window.open("{{ url('/') }}?demo=true", "_blank");
                } else {
                    const nameInput = document.querySelector("input[name='name']");
                    const passInput = document.querySelector("input[name='password']");

                    // page অনুযায়ী username ঠিক করো
                    let val = 'admin';
                    if (page === 'back_staff')    val = 'staff';
                    if (page === 'back_customer') val = 'james';

                    if (nameInput) nameInput.value = val;
                    if (passInput) passInput.value = val;

                    // 200ms পর form auto-submit
                    const form = document.getElementById('login-form');
                    if (form) setTimeout(() => form.submit(), 200);
                }
            }
        }

        // 7. Demo Button Logic (Event Delegation) — login page এর dropdown থেকে
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.demo-btn');
            if (!btn) return;

            e.preventDefault();
            const env = btn.getAttribute('data-env');
            const page = btn.getAttribute('data-page');
            const href = btn.getAttribute('href');

            setEnvCookie(env);

            if (env === '.env.ecom' && page === 'ecom_front') {
                window.open("{{ url('/') }}?demo=true", "_blank");
            } else {
                const nameInput = document.querySelector("input[name='name']");
                const passInput = document.querySelector("input[name='password']");
                
                let val = 'admin';
                if (page === 'back_staff') val = 'staff';
                else if (page === 'back_customer') val = 'james';

                if(nameInput) { nameInput.value = val; nameInput.focus(); }
                if(passInput) { passInput.value = val; passInput.focus(); }

                const form = document.getElementById('login-form');
                if(form) {
                    if(href) form.action = href;
                    form.submit();
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Toggle the dropdown when the button is clicked
        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('[data-toggle="dropdown"]');
            
            if (toggle) {
                event.preventDefault();
                const parent = toggle.parentElement;
                const menu = parent.querySelector('.dropdown-menu');
                const isOpen = parent.classList.contains('show');

                // Close all other open dropdowns first
                closeAllDropdowns();

                // Toggle the current one
                if (!isOpen) {
                    parent.classList.add('show');
                    menu.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                }
            } else if (!event.target.closest('.dropdown-menu')) {
                // 2. Close dropdowns if clicking outside the menu or toggle
                closeAllDropdowns();
            }
        });

        // Function to remove 'show' classes from all dropdown elements
        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown, .dropup').forEach(container => {
                container.classList.remove('show');
                const menu = container.querySelector('.dropdown-menu');
                const toggle = container.querySelector('[data-toggle="dropdown"]');
                if (menu) menu.classList.remove('show');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            });
        }

        // 3. Handle 'Esc' key to close dropdowns for accessibility
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    });
</script>

@include('backend.layout.toaster')
</body>
</html>