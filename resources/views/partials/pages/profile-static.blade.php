@php($locale = app()->getLocale())
<header class="header new-header profile-header">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo">
                                            <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="img-fluid" alt="">
                                                                <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_SECONDARY) }}" class="img-fluid pe-3" alt="">
                                    </a>
                                    <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo-small">
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="img-fluid" alt="">
                    </a>
                            </div>


            
            

            <ul class="nav header-navbar-rht">
                <!-- lang -->

                                                            <li class="nav-item">
                            <a hreflang="en" class="btn px-2" href="./en/profile">
                                EN
                            </a>
                        </li>
                                                                        
                
                

                <!-- User Menu -->
                                    <li class="nav-item dropdowns has-arrow logged-item">
                        <a href="javascript:void()" class="nav-link toggle">
                            <span class="log-user dropdown-toggle">
                                <span class="users-img">
                                    <img class="rounded-circle" src="{{ static_asset('assets/male.jpeg') }}" alt="Profile">
                                </span>
                                                                <div class="d-flex flex-column">

                                    <span class="user-text">
                                        sara -
                                    </span>
                                    <span class="user-role">  متدرب </span>
                                </div>
                            </span>
                        </a>
                        <div class="dropdown-menu list-group">

                                                                                                                <a class="dropdown-item" href="{{ route('profile', ['locale' => $locale]) }}">
<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M10.5459 2.438C10.7294 2.6417 10.9537 2.80457 11.2042 2.91605C11.4546 3.02754 11.7258 3.08514 11.9999 3.08514C12.2741 3.08514 12.5452 3.02754 12.7957 2.91605C13.0462 2.80457 13.2704 2.6417 13.4539 2.438L14.3999 1.4C14.671 1.09995 15.0284 0.891146 15.4229 0.802265C15.8174 0.713384 16.2298 0.748776 16.6034 0.903585C16.977 1.05839 17.2935 1.32503 17.5095 1.6669C17.7256 2.00877 17.8305 2.40912 17.8099 2.813L17.7389 4.213C17.7252 4.48611 17.7688 4.75907 17.867 5.01429C17.9652 5.26951 18.1157 5.50134 18.3089 5.69484C18.5021 5.88834 18.7337 6.03922 18.9888 6.13777C19.2439 6.23631 19.5168 6.28034 19.7899 6.267L21.1899 6.196C21.5935 6.17611 21.9934 6.28152 22.3348 6.49779C22.6762 6.71407 22.9423 7.03062 23.0967 7.40407C23.2511 7.77752 23.2863 8.18958 23.1973 8.58379C23.1084 8.97799 22.8997 9.33504 22.5999 9.606L21.5579 10.546C21.3545 10.7298 21.1918 10.9542 21.0805 11.2047C20.9692 11.4552 20.9117 11.7264 20.9117 12.0005C20.9117 12.2747 20.9692 12.5458 21.0805 12.7963C21.1918 13.0468 21.3545 13.2712 21.5579 13.455L22.5999 14.395C22.9 14.6661 23.1088 15.0235 23.1977 15.418C23.2865 15.8125 23.2511 16.2249 23.0963 16.5985C22.9415 16.9721 22.6749 17.2886 22.333 17.5046C21.9911 17.7206 21.5908 17.8256 21.1869 17.805L19.7869 17.734C19.5132 17.7199 19.2396 17.7635 18.9838 17.8618C18.7279 17.9601 18.4956 18.1111 18.3018 18.3049C18.108 18.4987 17.9571 18.731 17.8587 18.9869C17.7604 19.2427 17.7168 19.5163 17.7309 19.79L17.8019 21.19C17.8198 21.5919 17.7136 21.9895 17.4976 22.3289C17.2817 22.6682 16.9665 22.9329 16.5949 23.0869C16.2233 23.2409 15.8133 23.2768 15.4206 23.1897C15.0279 23.1026 14.6715 22.8967 14.3999 22.6L13.4589 21.559C13.2752 21.3556 13.0509 21.193 12.8005 21.0817C12.55 20.9704 12.279 20.9128 12.0049 20.9128C11.7308 20.9128 11.4598 20.9704 11.2094 21.0817C10.9589 21.193 10.7346 21.3556 10.5509 21.559L9.60592 22.6C9.33473 22.8981 8.97821 23.1053 8.58497 23.1934C8.19172 23.2814 7.78088 23.2461 7.40846 23.0921C7.03604 22.9382 6.72016 22.6731 6.50389 22.3331C6.28761 21.993 6.18147 21.5946 6.19992 21.192L6.27192 19.792C6.286 19.5183 6.24246 19.2447 6.14411 18.9889C6.04577 18.733 5.89482 18.5007 5.70102 18.3069C5.50722 18.1131 5.27489 17.9622 5.01907 17.8638C4.76325 17.7655 4.48963 17.7219 4.21592 17.736L2.81592 17.807C2.4122 17.8281 2.01186 17.7237 1.66986 17.5081C1.32785 17.2926 1.06095 16.9764 0.905801 16.6031C0.750649 16.2298 0.714851 15.8176 0.803323 15.4232C0.891794 15.0287 1.1002 14.6713 1.39992 14.4L2.44092 13.46C2.64437 13.2762 2.807 13.0518 2.91831 12.8013C3.02962 12.5508 3.08713 12.2797 3.08713 12.0055C3.08713 11.7314 3.02962 11.4602 2.91831 11.2097C2.807 10.9592 2.64437 10.7348 2.44092 10.551L1.39992 9.606C1.10117 9.33501 0.893402 8.97836 0.80502 8.58481C0.716638 8.19126 0.751954 7.78002 0.906141 7.4073C1.06033 7.03458 1.32586 6.71858 1.66644 6.50248C2.00702 6.28639 2.40603 6.18075 2.80892 6.2L4.20892 6.271C4.48315 6.28542 4.75734 6.242 5.0137 6.14354C5.27005 6.04509 5.50282 5.89382 5.69691 5.69955C5.89099 5.50528 6.04204 5.27235 6.14024 5.0159C6.23844 4.75946 6.2816 4.48522 6.26692 4.211L6.19992 2.81C6.18102 2.40727 6.28685 2.00851 6.50296 1.66815C6.71907 1.32778 7.03494 1.06238 7.40746 0.908167C7.77998 0.753953 8.19101 0.718434 8.58446 0.806457C8.97791 0.89448 9.33462 1.10176 9.60592 1.4L10.5459 2.438Z" stroke="#8e8e8e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" stroke="#8e8e8e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                               الملف الشخصي                            </a>
                            <a class="dropdown-item log-out" href="{{ route('home', ['locale' => $locale]) }}" onclick="event.preventDefault();document.getElementById(&#39;logout-form&#39;).submit();">
                               <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11985 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H15M10 7L15 12M15 12L10 17M15 12L3 12" stroke="#e82646" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                تسجيل الخروج                            </a>
                            </div>
                    </li>
                                <!-- /User Menu -->


            </ul>
        </nav>
    </div>
</header>
<!-- /Header -->
<!-- /ramzy -->
    
    <!-- Page Content -->
    <div class="new-profile-wrapper" style="transform: none;">

                    <!-- Sidebar -->
<div class="new-sidebar theiaStickySidebar" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
    
<div class="theiaStickySidebar" style="padding-top: 1px; padding-bottom: 0px; position: static; transform: none;"><div class="user-sidebar">
        <div class="user-head">
            <span class="flex-shrink-0">
                <img src="{{ static_asset('assets/male.jpeg') }}" class="img-fluid" alt="img">
            </span>
            <div class="user-information">
                <div>
                    <h6>sara - el3taby</h6>
                    <ul>
                        <li> sara590@ubt.edu.sa </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="user-body">
            <ul>
                <li>
                    <a href="{{ route('profile', ['locale' => $locale]) }}" class="active">
                       <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M2 12.2039C2 9.91549 2 8.77128 2.5192 7.82274C3.0384 6.87421 3.98695 6.28551 5.88403 5.10813L7.88403 3.86687C9.88939 2.62229 10.8921 2 12 2C13.1079 2 14.1106 2.62229 16.116 3.86687L18.116 5.10812C20.0131 6.28551 20.9616 6.87421 21.4808 7.82274C22 8.77128 22 9.91549 22 12.2039V13.725C22 17.6258 22 19.5763 20.8284 20.7881C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.7881C2 19.5763 2 17.6258 2 13.725V12.2039Z" stroke="var(--primary)" stroke-width="1.5"></path> <path d="M15 18H9" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                        لوحة التحكم                    </a>
                </li>
                 <hr class="my-2">
                                <li>
                    <a href="{{ route('learning-list', ['locale' => $locale]) }}" class=" d-flex">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M19.8978 16H7.89778C6.96781 16 6.50282 16 6.12132 16.1022C5.08604 16.3796 4.2774 17.1883 4 18.2235" stroke="var(--primary)" stroke-width="1.5"></path> <path d="M8 7H16" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M8 10.5H13" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M13 16V19.5309C13 19.8065 13 19.9443 12.9051 20C12.8103 20.0557 12.6806 19.9941 12.4211 19.8708L11.1789 19.2808C11.0911 19.2391 11.0472 19.2182 11 19.2182C10.9528 19.2182 10.9089 19.2391 10.8211 19.2808L9.57889 19.8708C9.31943 19.9941 9.18971 20.0557 9.09485 20C9 19.9443 9 19.8065 9 19.5309V16.45" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M10 22C7.17157 22 5.75736 22 4.87868 21.1213C4 20.2426 4 18.8284 4 16V8C4 5.17157 4 3.75736 4.87868 2.87868C5.75736 2 7.17157 2 10 2H14C16.8284 2 18.2426 2 19.1213 2.87868C20 3.75736 20 5.17157 20 8M14 22C16.8284 22 18.2426 22 19.1213 21.1213C20 20.2426 20 18.8284 20 16V12" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                            قائمة التعلم                    </a>
                </li>
                                                
                                <li>
                    <a href="{{ legacy_page('ar/certificates.html') }}" class=" d-flex">
                        <svg fill="var(--primary)" width="20px" viewBox="0 0 32.00 32.00" xmlns="http://www.w3.org/2000/svg" stroke="var(--primary)" stroke-width="0.00032"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M 16 3 C 15.375 3 14.753906 3.210938 14.21875 3.59375 L 12.5625 4.75 L 10.65625 5 L 10.625 5 L 10.59375 5.03125 C 9.320313 5.316406 8.316406 6.320313 8.03125 7.59375 L 8 7.625 L 8 7.65625 L 7.75 9.59375 L 6.59375 11.09375 L 6.5625 11.125 L 6.5625 11.15625 C 5.863281 12.273438 5.832031 13.714844 6.59375 14.78125 L 7.78125 16.4375 L 8.09375 18.15625 L 4.875 23.0625 L 3.84375 24.59375 L 8.625 24.59375 L 9.78125 27.28125 L 10.5 29 L 11.53125 27.4375 L 14.6875 22.6875 C 15.535156 23.035156 16.492188 23.066406 17.3125 22.6875 L 20.46875 27.4375 L 21.5 29 L 22.21875 27.28125 L 23.375 24.59375 L 28.15625 24.59375 L 27.125 23.0625 L 24 18.3125 L 24.25 16.4375 L 25.40625 14.78125 L 25.4375 14.75 L 25.4375 14.71875 C 26.136719 13.601563 26.167969 12.191406 25.40625 11.125 L 24.25 9.46875 L 23.875 7.59375 L 23.90625 7.59375 C 23.902344 7.570313 23.878906 7.554688 23.875 7.53125 C 23.695313 6.222656 22.660156 5.160156 21.34375 5 L 21.3125 5 L 19.4375 4.75 L 17.78125 3.59375 C 17.246094 3.210938 16.625 3 16 3 Z M 16 5.03125 C 16.230469 5.03125 16.457031 5.101563 16.625 5.21875 L 18.40625 6.5 L 18.625 6.65625 L 18.875 6.6875 L 21.0625 7 L 21.09375 7 C 21.542969 7.050781 21.855469 7.363281 21.90625 7.8125 L 21.90625 7.875 L 22.3125 10.09375 L 22.34375 10.3125 L 22.5 10.5 L 23.78125 12.28125 C 24.019531 12.613281 24.050781 13.175781 23.75 13.65625 L 22.34375 15.625 L 22.3125 15.875 L 22 18.0625 L 22 18.09375 C 21.980469 18.257813 21.925781 18.410156 21.84375 18.53125 L 21.78125 18.5625 L 21.78125 18.59375 C 21.636719 18.765625 21.4375 18.878906 21.1875 18.90625 L 21.125 18.90625 L 18.84375 19.3125 L 18.59375 19.34375 L 18.40625 19.5 L 16.625 20.78125 C 16.292969 21.019531 15.699219 21.050781 15.21875 20.75 L 13.59375 19.5 L 13.40625 19.34375 L 13.125 19.3125 L 10.9375 19 L 10.90625 19 C 10.597656 18.964844 10.359375 18.804688 10.21875 18.5625 C 10.15625 18.453125 10.109375 18.324219 10.09375 18.1875 L 10.09375 18.125 L 9.6875 15.84375 L 9.65625 15.59375 L 9.5 15.40625 L 8.21875 13.625 C 7.980469 13.292969 7.949219 12.699219 8.25 12.21875 L 9.5 10.59375 L 9.65625 10.40625 L 9.6875 10.125 L 9.96875 8.03125 C 9.972656 8.015625 9.996094 8.015625 10 8 C 10.125 7.511719 10.511719 7.125 11 7 C 11.015625 6.996094 11.015625 6.972656 11.03125 6.96875 L 13.125 6.6875 L 13.375 6.65625 L 13.59375 6.5 L 15.375 5.21875 C 15.542969 5.101563 15.769531 5.03125 16 5.03125 Z M 22.90625 20.25 L 24.4375 22.59375 L 22.03125 22.59375 L 21.78125 23.21875 L 21.09375 24.8125 L 18.96875 21.5625 L 19.4375 21.21875 L 21.40625 20.875 L 21.40625 20.90625 C 21.429688 20.902344 21.445313 20.878906 21.46875 20.875 C 22.007813 20.800781 22.496094 20.574219 22.90625 20.25 Z M 9.09375 20.28125 C 9.519531 20.664063 10.0625 20.929688 10.65625 21 C 10.667969 21 10.675781 21 10.6875 21 L 12.59375 21.25 L 13.03125 21.59375 L 10.90625 24.8125 L 10.21875 23.21875 L 9.96875 22.59375 L 7.5625 22.59375 Z"></path></g></svg>
                            الشهادات                    </a>
                </li>
                <li>
                    <a href="{{ legacy_page('ar/statements.html') }}" class=" d-flex">
                             <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 12H9M16 12H12" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M16 8H15M12 8H8" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M8 16H13" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M3 14V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H13C16.7712 2 18.6569 2 19.8284 3.17157C20.4816 3.82476 20.7706 4.69989 20.8985 6M21 10V14C21 17.7712 21 19.6569 19.8284 20.8284C18.6569 22 16.7712 22 13 22H11C7.22876 22 5.34315 22 4.17157 20.8284C3.51839 20.1752 3.22937 19.3001 3.10149 18" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                                الإفادات                        </a>
                </li>
                
                
                                <li>
                    <a href="{{ legacy_page('ar/my-orders.html') }}" class="">
                       <svg width="24px" height="24px" viewBox="0 0 1024 1024" fill="var(--primary)" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M959.018 208.158c0.23-2.721 0.34-5.45 0.34-8.172 0-74.93-60.96-135.89-135.89-135.89-1.54 0-3.036 0.06-6.522 0.213l-611.757-0.043c-1.768-0.085-3.563-0.17-5.424-0.17-74.812 0-135.67 60.84-135.67 135.712l0.188 10.952h-0.306l0.391 594.972-0.162 20.382c0 74.03 60.22 134.25 134.24 134.25 1.668 0 7.007-0.239 7.1-0.239l608.934 0.085c2.985 0.357 6.216 0.468 9.55 0.468 35.815 0 69.514-13.954 94.879-39.302 25.373-25.34 39.344-58.987 39.344-94.794l-0.145-12.015h0.918l-0.008-606.41z m-757.655 693.82l-2.585-0.203c-42.524 0-76.146-34.863-76.537-79.309V332.671H900.79l0.46 485.186-0.885 2.865c-0.535 1.837-0.8 3.58-0.8 5.17 0 40.382-31.555 73.766-71.852 76.002l-10.816 0.621v-0.527l-615.533-0.01zM900.78 274.424H122.3l-0.375-65.934 0.85-2.924c0.52-1.82 0.782-3.63 0.782-5.247 0-42.236 34.727-76.665 78.179-76.809l0.45-0.068 618.177 0.018 2.662 0.203c42.329 0 76.767 34.439 76.767 76.768 0 1.326 0.196 2.687 0.655 4.532l0.332 0.884v68.577z" fill=""></path><path d="M697.67 471.435c-7.882 0-15.314 3.078-20.918 8.682l-223.43 223.439L346.599 596.84c-5.544-5.603-12.95-8.69-20.842-8.69s-15.323 3.078-20.918 8.665c-5.578 5.518-8.674 12.9-8.7 20.79-0.017 7.908 3.07 15.357 8.69 20.994l127.55 127.558c5.57 5.56 13.01 8.622 20.943 8.622 7.925 0 15.364-3.06 20.934-8.63l244.247-244.247c5.578-5.511 8.674-12.883 8.7-20.783 0.017-7.942-3.079-15.408-8.682-20.986-5.552-5.612-12.958-8.698-20.85-8.698z" fill=""></path></g></svg>
                            طلباتي                    </a>
                </li>
                <li>
                    <a href="{{ legacy_page('user-requests.html') }}" class="">
                        <svg width="24px" height="24px" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" stroke-width="3" stroke="var(--primary)" fill="none"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M53.79,40.85a23.82,23.82,0,0,1-2.63,4.78A23.51,23.51,0,0,1,8.87,36.22" stroke-linecap="round"></path><path d="M10.37,22.77A23.51,23.51,0,0,1,55.1,27.64" stroke-linecap="round"></path><polyline points="45.9 22.36 55.23 28.02 60.45 19.22" stroke-linecap="round"></polyline><polyline points="17.99 41.2 8.66 35.53 3.44 44.34" stroke-linecap="round"></polyline><path d="M40.32,43.14H24.79a.12.12,0,0,1-.1-.2c1.06-1.14,6.15-7,4.2-12.19-2.16-5.84-.76-15.11,9.72-10.8" stroke-linecap="round"></path><line x1="22.33" y1="30.9" x2="36.83" y2="30.9" stroke-linecap="round"></line></g></svg>
                            طلبات الاسترداد                    </a>
                </li>
                                 
                

                <li>
                    <a href="{{ legacy_page('certificate-verify.html') }}" class="">
                        <!-- Verification / Check Icon -->
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L4 5V11C4 16 7.5 19.5 12 21
                                        C16.5 19.5 20 16 20 11V5L12 2Z" stroke="var(--primary)" stroke-width="1.5"></path>
                                <path d="M9 12L11 14L15 10" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            التحقق من الشهادة                    </a>
                </li>
                <li>
                    <a href="{{ legacy_page('account-settings.html') }}" class="">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M10.5459 2.438C10.7294 2.6417 10.9537 2.80457 11.2042 2.91605C11.4546 3.02754 11.7258 3.08514 11.9999 3.08514C12.2741 3.08514 12.5452 3.02754 12.7957 2.91605C13.0462 2.80457 13.2704 2.6417 13.4539 2.438L14.3999 1.4C14.671 1.09995 15.0284 0.891146 15.4229 0.802265C15.8174 0.713384 16.2298 0.748776 16.6034 0.903585C16.977 1.05839 17.2935 1.32503 17.5095 1.6669C17.7256 2.00877 17.8305 2.40912 17.8099 2.813L17.7389 4.213C17.7252 4.48611 17.7688 4.75907 17.867 5.01429C17.9652 5.26951 18.1157 5.50134 18.3089 5.69484C18.5021 5.88834 18.7337 6.03922 18.9888 6.13777C19.2439 6.23631 19.5168 6.28034 19.7899 6.267L21.1899 6.196C21.5935 6.17611 21.9934 6.28152 22.3348 6.49779C22.6762 6.71407 22.9423 7.03062 23.0967 7.40407C23.2511 7.77752 23.2863 8.18958 23.1973 8.58379C23.1084 8.97799 22.8997 9.33504 22.5999 9.606L21.5579 10.546C21.3545 10.7298 21.1918 10.9542 21.0805 11.2047C20.9692 11.4552 20.9117 11.7264 20.9117 12.0005C20.9117 12.2747 20.9692 12.5458 21.0805 12.7963C21.1918 13.0468 21.3545 13.2712 21.5579 13.455L22.5999 14.395C22.9 14.6661 23.1088 15.0235 23.1977 15.418C23.2865 15.8125 23.2511 16.2249 23.0963 16.5985C22.9415 16.9721 22.6749 17.2886 22.333 17.5046C21.9911 17.7206 21.5908 17.8256 21.1869 17.805L19.7869 17.734C19.5132 17.7199 19.2396 17.7635 18.9838 17.8618C18.7279 17.9601 18.4956 18.1111 18.3018 18.3049C18.108 18.4987 17.9571 18.731 17.8587 18.9869C17.7604 19.2427 17.7168 19.5163 17.7309 19.79L17.8019 21.19C17.8198 21.5919 17.7136 21.9895 17.4976 22.3289C17.2817 22.6682 16.9665 22.9329 16.5949 23.0869C16.2233 23.2409 15.8133 23.2768 15.4206 23.1897C15.0279 23.1026 14.6715 22.8967 14.3999 22.6L13.4589 21.559C13.2752 21.3556 13.0509 21.193 12.8005 21.0817C12.55 20.9704 12.279 20.9128 12.0049 20.9128C11.7308 20.9128 11.4598 20.9704 11.2094 21.0817C10.9589 21.193 10.7346 21.3556 10.5509 21.559L9.60592 22.6C9.33473 22.8981 8.97821 23.1053 8.58497 23.1934C8.19172 23.2814 7.78088 23.2461 7.40846 23.0921C7.03604 22.9382 6.72016 22.6731 6.50389 22.3331C6.28761 21.993 6.18147 21.5946 6.19992 21.192L6.27192 19.792C6.286 19.5183 6.24246 19.2447 6.14411 18.9889C6.04577 18.733 5.89482 18.5007 5.70102 18.3069C5.50722 18.1131 5.27489 17.9622 5.01907 17.8638C4.76325 17.7655 4.48963 17.7219 4.21592 17.736L2.81592 17.807C2.4122 17.8281 2.01186 17.7237 1.66986 17.5081C1.32785 17.2926 1.06095 16.9764 0.905801 16.6031C0.750649 16.2298 0.714851 15.8176 0.803323 15.4232C0.891794 15.0287 1.1002 14.6713 1.39992 14.4L2.44092 13.46C2.64437 13.2762 2.807 13.0518 2.91831 12.8013C3.02962 12.5508 3.08713 12.2797 3.08713 12.0055C3.08713 11.7314 3.02962 11.4602 2.91831 11.2097C2.807 10.9592 2.64437 10.7348 2.44092 10.551L1.39992 9.606C1.10117 9.33501 0.893402 8.97836 0.80502 8.58481C0.716638 8.19126 0.751954 7.78002 0.906141 7.4073C1.06033 7.03458 1.32586 6.71858 1.66644 6.50248C2.00702 6.28639 2.40603 6.18075 2.80892 6.2L4.20892 6.271C4.48315 6.28542 4.75734 6.242 5.0137 6.14354C5.27005 6.04509 5.50282 5.89382 5.69691 5.69955C5.89099 5.50528 6.04204 5.27235 6.14024 5.0159C6.23844 4.75946 6.2816 4.48522 6.26692 4.211L6.19992 2.81C6.18102 2.40727 6.28685 2.00851 6.50296 1.66815C6.71907 1.32778 7.03494 1.06238 7.40746 0.908167C7.77998 0.753953 8.19101 0.718434 8.58446 0.806457C8.97791 0.89448 9.33462 1.10176 9.60592 1.4L10.5459 2.438Z" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                            الإعدادات                    </a>
                </li>
                 <li>
                    <a href="{{ route('home', ['locale' => $locale]) }}" onclick="event.preventDefault();document.getElementById(&#39;logout-form&#39;).submit();" class="">
                      <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11985 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H15M10 7L15 12M15 12L10 17M15 12L3 12" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                            تسجيل الخروج
                    </a>
                </li>
                </ul>
        </div>
    </div></div></div>
<!-- /Sidebar -->
        

        <div class="dashboard-header">

            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('profile', ['locale' => $locale]) }}"> لوحة التحكم   </a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">    الملف الشخصي    </li>

                </ol>
            </nav>

        </div>


        <div class="new-design-content account-stats">

            <div class="container">

                                    <div class="alert alert-danger">
                        <strong>بعض بيانات الحساب الاساسية مفقودة</strong>
                        <ul>
                                                                                        <li>
                                    <a href="{{ legacy_page('account-settings.html') }}" class="text-primary text-decoration-underline">
                                        من فضلك ادخل الجنسية                                    </a>
                                </li>
                                                                                        <li>
                                    <a href="{{ legacy_page('account-settings.html') }}" class="text-primary text-decoration-underline">
                                        من فضلك ادخل رقم الهوية الوطنية                                    </a>
                                </li>
                                                    </ul>
                    </div>

                <!-- ملخص المستخدم -->
                <div class="profile-dashboard-hero settings-card mb-4">
                    <div class="settings-card-body py-4 px-3 px-md-4">
                        <div class="row align-items-center g-4">
                            <div class="col-auto">
                                <img src="{{ static_asset('assets/male.jpeg') }}" class="rounded-circle profile-dashboard-hero__avatar" width="96" height="96" alt="صورة الملف الشخصي">
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h2 class="h4 mb-0">sara - el3taby</h2>
                                    <span class="badge bg-primary-light text-dark">متدرب</span>
                                    <span class="badge bg-warning text-dark">بيانات أساسية ناقصة</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 profile-dashboard-hero__meta">
                                    <li><i class="fa fa-envelope ms-1" aria-hidden="true"></i> sara590@ubt.edu.sa</li>
                                    <li><i class="fa fa-phone ms-1" aria-hidden="true"></i> +966 57 292 7309</li>
                                    <li><i class="fa fa-calendar ms-1" aria-hidden="true"></i> عضو منذ: مايو 2026</li>
                                    <li><i class="fa fa-id-card ms-1" aria-hidden="true"></i> معرّف الحساب الداخلي: <span class="text-dark">#USR-DEMO-10492</span></li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg-auto text-lg-start">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <a href="{{ legacy_page('account-settings.html') }}" class="btn btn-primary"><i class="fa fa-pen ms-1" aria-hidden="true"></i> تعديل البيانات</a>
                                    <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-outline-primary">قائمة التعلم</a>
                                    <a href="{{ legacy_page('certificate-verify.html') }}" class="btn btn-outline-secondary">التحقق من شهادة</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview -->
                <div class="row g-3 mb-4">
                                        
                                                                                
                                            <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon">
                                        <svg width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z" stroke="var(--primary)" stroke-width="1.5"></path> <path d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z" stroke="var(--primary)" stroke-width="1.5"></path> <path d="M2.26121 3.09184L2.50997 2.38429H2.50997L2.26121 3.09184ZM2.24876 2.29246C1.85799 2.15507 1.42984 2.36048 1.29246 2.75124C1.15507 3.14201 1.36048 3.57016 1.75124 3.70754L2.24876 2.29246ZM4.58584 4.32298L5.20507 3.89983V3.89983L4.58584 4.32298ZM5.88772 14.5862L5.34345 15.1022H5.34345L5.88772 14.5862ZM20.6578 9.88275L21.3923 10.0342L21.3933 10.0296L20.6578 9.88275ZM20.158 12.3075L20.8926 12.4589L20.158 12.3075ZM20.7345 6.69708L20.1401 7.15439L20.7345 6.69708ZM19.1336 15.0504L18.6598 14.469L19.1336 15.0504ZM5.70808 9.76V7.03836H4.20808V9.76H5.70808ZM2.50997 2.38429L2.24876 2.29246L1.75124 3.70754L2.01245 3.79938L2.50997 2.38429ZM10.9375 16.25H16.2404V14.75H10.9375V16.25ZM5.70808 7.03836C5.70808 6.3312 5.7091 5.7411 5.65719 5.26157C5.60346 4.76519 5.48705 4.31247 5.20507 3.89983L3.96661 4.74613C4.05687 4.87822 4.12657 5.05964 4.1659 5.42299C4.20706 5.8032 4.20808 6.29841 4.20808 7.03836H5.70808ZM2.01245 3.79938C2.68006 4.0341 3.11881 4.18965 3.44166 4.34806C3.74488 4.49684 3.87855 4.61727 3.96661 4.74613L5.20507 3.89983C4.92089 3.48397 4.54304 3.21763 4.10241 3.00143C3.68139 2.79485 3.14395 2.60719 2.50997 2.38429L2.01245 3.79938ZM4.20808 9.76C4.20808 11.2125 4.22171 12.2599 4.35876 13.0601C4.50508 13.9144 4.79722 14.5261 5.34345 15.1022L6.43198 14.0702C6.11182 13.7325 5.93913 13.4018 5.83723 12.8069C5.72607 12.1578 5.70808 11.249 5.70808 9.76H4.20808ZM10.9375 14.75C9.52069 14.75 8.53763 14.7482 7.79696 14.6432C7.08215 14.5418 6.70452 14.3576 6.43198 14.0702L5.34345 15.1022C5.93731 15.7286 6.69012 16.0013 7.58636 16.1283C8.45674 16.2518 9.56535 16.25 10.9375 16.25V14.75ZM4.95808 6.87H17.0888V5.37H4.95808V6.87ZM19.9232 9.73135L19.4235 12.1561L20.8926 12.4589L21.3923 10.0342L19.9232 9.73135ZM17.0888 6.87C17.9452 6.87 18.6989 6.871 19.2937 6.93749C19.5893 6.97053 19.8105 7.01643 19.9659 7.07105C20.1273 7.12776 20.153 7.17127 20.1401 7.15439L21.329 6.23978C21.094 5.93436 20.7636 5.76145 20.4632 5.65587C20.1567 5.54818 19.8101 5.48587 19.4604 5.44678C18.7646 5.369 17.9174 5.37 17.0888 5.37V6.87ZM21.3933 10.0296C21.5625 9.18167 21.7062 8.47024 21.7414 7.90038C21.7775 7.31418 21.7108 6.73617 21.329 6.23978L20.1401 7.15439C20.2021 7.23508 20.2706 7.38037 20.2442 7.80797C20.2168 8.25191 20.1002 8.84478 19.9223 9.73595L21.3933 10.0296ZM16.2404 16.25C17.0021 16.25 17.6413 16.2513 18.1566 16.1882C18.6923 16.1227 19.1809 15.9794 19.6074 15.6318L18.6598 14.469C18.5346 14.571 18.3571 14.6525 17.9744 14.6994C17.5712 14.7487 17.0397 14.75 16.2404 14.75V16.25ZM19.4235 12.1561C19.2621 12.9389 19.1535 13.4593 19.0238 13.8442C18.9007 14.2095 18.785 14.367 18.6598 14.469L19.6074 15.6318C20.0339 15.2842 20.2729 14.8346 20.4453 14.3232C20.6111 13.8312 20.7388 13.2049 20.8926 12.4589L19.4235 12.1561Z" fill="var(--primary)"></path> <path d="M9.5 9L10.0282 12.1179" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> <path d="M15.5283 9L15.0001 12.1179" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                                </span>
                                <p> في سلة التسوق الخاصة بك </p>
                                <h3>0</h3>
                            </div>
                        </div>
                                                                                    <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon">
                                    <svg width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.96173 18.9109L9.42605 18.3219L8.96173 18.9109ZM12 5.50063L11.4596 6.02073C11.463 6.02421 11.4664 6.02765 11.4698 6.03106L12 5.50063ZM15.0383 18.9109L15.5026 19.4999L15.0383 18.9109ZM13.4698 8.03034C13.7627 8.32318 14.2376 8.32309 14.5304 8.03014C14.8233 7.7372 14.8232 7.26232 14.5302 6.96948L13.4698 8.03034ZM9.42605 18.3219C7.91039 17.1271 6.25307 15.9603 4.93829 14.4798C3.64922 13.0282 2.75 11.3345 2.75 9.1371H1.25C1.25 11.8026 2.3605 13.8361 3.81672 15.4758C5.24723 17.0866 7.07077 18.3752 8.49742 19.4999L9.42605 18.3219ZM2.75 9.1371C2.75 6.98623 3.96537 5.18252 5.62436 4.42419C7.23607 3.68748 9.40166 3.88258 11.4596 6.02073L12.5404 4.98053C10.0985 2.44352 7.26409 2.02539 5.00076 3.05996C2.78471 4.07292 1.25 6.42503 1.25 9.1371H2.75ZM8.49742 19.4999C9.00965 19.9037 9.55954 20.3343 10.1168 20.6599C10.6739 20.9854 11.3096 21.25 12 21.25V19.75C11.6904 19.75 11.3261 19.6293 10.8736 19.3648C10.4213 19.1005 9.95208 18.7366 9.42605 18.3219L8.49742 19.4999ZM15.5026 19.4999C16.9292 18.3752 18.7528 17.0866 20.1833 15.4758C21.6395 13.8361 22.75 11.8026 22.75 9.1371H21.25C21.25 11.3345 20.3508 13.0282 19.0617 14.4798C17.7469 15.9603 16.0896 17.1271 14.574 18.3219L15.5026 19.4999ZM22.75 9.1371C22.75 6.42503 21.2153 4.07292 18.9992 3.05996C16.7359 2.02539 13.9015 2.44352 11.4596 4.98053L12.5404 6.02073C14.5983 3.88258 16.7639 3.68748 18.3756 4.42419C20.0346 5.18252 21.25 6.98623 21.25 9.1371H22.75ZM14.574 18.3219C14.0479 18.7366 13.5787 19.1005 13.1264 19.3648C12.6739 19.6293 12.3096 19.75 12 19.75V21.25C12.6904 21.25 13.3261 20.9854 13.8832 20.6599C14.4405 20.3343 14.9903 19.9037 15.5026 19.4999L14.574 18.3219ZM11.4698 6.03106L13.4698 8.03034L14.5302 6.96948L12.5302 4.97021L11.4698 6.03106Z" fill="var(--primary)"></path> </g></svg>
                                </span>
                                <p> قائمة المفضلات </p>
                                <h3>0</h3>
                            </div>
                        </div>
                                                                                    <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon dash-icon--fa" aria-hidden="true"><i class="fa-solid fa-graduation-cap fa-xl"></i></span>
                                <p> دورات مسجّلة / نشطة </p>
                                <h3>0</h3>
                            </div>
                        </div>
                                                                                    <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon dash-icon--fa" aria-hidden="true"><i class="fa-solid fa-award fa-xl"></i></span>
                                <p> شهادات صادرة </p>
                                <h3>0</h3>
                            </div>
                        </div>
                                                                                    <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon dash-icon--fa" aria-hidden="true"><i class="fa-solid fa-clock fa-xl"></i></span>
                                <p> ساعات تعلّم مقدّرة </p>
                                <h3>—</h3>
                            </div>
                        </div>
                                                                                    <div class="col-xl-4 col-sm-6 d-flex">
                            <div class="dash-widget flex-fill">
                                <span class="dash-icon dash-icon--fa" aria-hidden="true"><i class="fa-solid fa-file-invoice-dollar fa-xl"></i></span>
                                <p> طلبات استرداد مفتوحة </p>
                                <h3><a href="{{ legacy_page('user-requests.html') }}" class="text-body text-decoration-none">0</a></h3>
                            </div>
                        </div>
                                    </div>

                <!-- تفاصيل الحساب (عرض فقط — يُربَط لاحقاً بالـ API) -->
                <div class="settings-card mb-4">
                    <div class="settings-card-head">
                        <h4>تفاصيل الحساب</h4>
                        <span>معاينة للبيانات المطلوبة في لوحة المتدرب؛ التعديل من صفحة الإعدادات.</span>
                    </div>
                    <div class="settings-card-body pt-0">
                        <div class="row g-0 profile-detail-grid">
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">الاسم الكامل</span>
                                <span class="profile-detail-value">sara - el3taby</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">البريد الإلكتروني</span>
                                <span class="profile-detail-value">sara590@ubt.edu.sa</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">رقم الجوال</span>
                                <span class="profile-detail-value" dir="ltr">+966 57 292 7309</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">نوع الحساب</span>
                                <span class="profile-detail-value">متدرب</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">الجنسية</span>
                                <span class="profile-detail-value"><a href="{{ legacy_page('account-settings.html') }}" class="text-warning text-decoration-underline">غير مكتمل — أضف الجنسية</a></span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">الهوية الوطنية / الإقامة</span>
                                <span class="profile-detail-value"><a href="{{ legacy_page('account-settings.html') }}" class="text-warning text-decoration-underline">غير مكتمل — أضف الرقم</a></span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">المدينة</span>
                                <span class="profile-detail-value text-muted">—</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">تاريخ التسجيل</span>
                                <span class="profile-detail-value">مايو 2026</span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">حالة البريد</span>
                                <span class="profile-detail-value"><span class="badge bg-light text-success border">مفعّل</span></span>
                            </div>
                            <div class="col-md-6 profile-detail-item">
                                <span class="profile-detail-label">تفضيلات الإشعارات</span>
                                <span class="profile-detail-value text-muted">البريد والمنصّة <small>(يمكن ضبطها لاحقاً من الإعدادات)</small></span>
                            </div>
                        </div>
                        <p class="small text-muted mb-0 mt-3"><i class="fa fa-info-circle ms-1 text-primary"></i> القيم المعروضة للتجربة المحلية؛ عند الربط بالخادم تُستبدل تلقائياً.</p>
                    </div>
                </div>

                <!-- اختصارات سريعة -->
                <div class="mb-2">
                    <h3 class="section-title-profile">اختصارات سريعة</h3>
                    <p class="text-muted small mb-3">الوصول إلى أهم صفحات رحلتك التدريبية من لوحة واحدة.</p>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
                                    <div>
                                        <div class="fw-bold">قائمة التعلم</div>
                                        <small class="text-muted">دوراتك وجلساتك القادمة</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
                                    <div>
                                        <div class="fw-bold">تصفح الدورات</div>
                                        <small class="text-muted">استكشاف البرامج المتاحة</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ legacy_page('certificate-verify.html') }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-certificate"></i></span>
                                    <div>
                                        <div class="fw-bold">شهاداتي</div>
                                        <small class="text-muted">عرض الشهادات الصادرة</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ legacy_page('user-requests.html') }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
                                    <div>
                                        <div class="fw-bold">طلبات الاسترداد</div>
                                        <small class="text-muted">متابعة حالة الطلبات</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ legacy_page('cooperative-training.html') }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
                                    <div>
                                        <div class="fw-bold">التدريب التعاوني</div>
                                        <small class="text-muted">معلومات برامج التعاون</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('contact', ['locale' => $locale]) }}" class="profile-quick-card card h-100 text-decoration-none text-body">
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="profile-quick-card__icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                                    <div>
                                        <div class="fw-bold">الدعم والتواصل</div>
                                        <small class="text-muted">تواصل مع فريق المنصة</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </div>
    <!-- /Page Content -->

            <a href="tel:+966543406744" class="float-call">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="#ffffff" stroke-width="1.5" stroke-miterlimit="10"></path> </g></svg>
        </a>