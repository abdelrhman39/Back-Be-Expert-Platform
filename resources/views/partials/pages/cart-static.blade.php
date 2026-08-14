@php($locale = app()->getLocale())
<div class="page-content content d-lg-flex py-0">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="dashboard-header">
                        <div class="main-titlee">
                            <h3>سلة التسوق</h3>
                        </div>
                        <div class="head-info">
                            <p>عدد الدورات <span class="text-primary" id="countCartCourses">(0)</span></p>
                        </div>
                    </div>

                    <div class="row" id="cartListContainer">
                        <div class="col-md-12 col-lg-12 col-xl-12 mb-3 text-center">
                            <div class="w-100 text-center mt-4">
                                <div class="empty-cart">
                                    <img src="{{ static_asset('assets/vendor/images/site-favicon.png') }}" alt="" style="max-width:120px;opacity:.35;">
                                </div>
                                <p class="text-muted mt-3 mb-4">سلة التسوق فارغة حالياً</p>
                                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">اكتشف دوراتنا</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-4 chekout-login">
                <span class="text-danger">لإتمام الشراء يرجى
                    <a class="text-success" href="{{ route('login', ['locale' => $locale]) }}">تسجيل الدخول</a> أو
                    <a class="text-success" href="{{ legacy_page('ar/register/index.html') }}">إنشاء حساب</a>
                </span>
            </div>
        </div>
    </div>