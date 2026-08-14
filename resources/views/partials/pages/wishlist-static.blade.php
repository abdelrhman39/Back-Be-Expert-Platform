@php($locale = app()->getLocale())
<div class="page-content content">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="dashboard-header">
                        <div class="main-titlee">
                            <h3>قائمة المفضلة</h3>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="row" id="wishlistContainer">
                            <div class="col-md-12 col-lg-12 col-xl-12 mb-3 text-center">
                                <div class="w-100 text-center mt-4">
                                    <div class="empty-cart">
                                        <img src="{{ static_asset('assets/vendor/images/site-favicon.png') }}" alt="" style="max-width:120px;opacity:.35;">
                                    </div>
                                    <p class="text-muted mt-3 mb-4">لا توجد دورات في قائمة المفضلة</p>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">اكتشف دوراتنا</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>