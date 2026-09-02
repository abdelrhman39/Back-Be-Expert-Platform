@php($locale = app()->getLocale() ?? 'ar')
<script>
    window.domainCommerce = {
        cartAddUrl: @json(route('cart.add', ['locale' => $locale])),
        wishlistToggleUrl: @json(route('wishlist.toggle', ['locale' => $locale])),
        csrfToken: @json(csrf_token()),
    };

    (function () {
        if (window.__domainCommerceBridgeLoaded) {
            return;
        }
        window.__domainCommerceBridgeLoaded = true;

        function csrfHeaders() {
            return {
                'X-CSRF-TOKEN': window.domainCommerce.csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
        }

        function normalizeCounts(data) {
            if (!data) {
                return {};
            }

            return {
                cart_count: data.cart_count ?? data.cartCount,
                wishlist_count: data.wishlist_count ?? data.wishlistCount,
            };
        }

        function syncBadge(el, count) {
            if (!el) {
                return;
            }

            el.textContent = count;
            el.style.display = Number(count) > 0 ? '' : 'none';
        }

        function updateCounts(data) {
            var counts = normalizeCounts(data);

            if (typeof counts.cart_count !== 'undefined' && counts.cart_count !== null) {
                syncBadge(document.getElementById('cartCount'), counts.cart_count);
                var countCartCourses = document.getElementById('countCartCourses');
                if (countCartCourses) {
                    countCartCourses.textContent = '(' + counts.cart_count + ')';
                }
            }

            if (typeof counts.wishlist_count !== 'undefined' && counts.wishlist_count !== null) {
                syncBadge(document.getElementById('wichCount'), counts.wishlist_count);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            syncBadge(document.getElementById('cartCount'), document.getElementById('cartCount')?.textContent || 0);
            syncBadge(document.getElementById('wichCount'), document.getElementById('wichCount')?.textContent || 0);
        });

        window.domainUpdateCommerceCounts = updateCounts;

        function showToast(message, type) {
            if (typeof toastr !== 'undefined' && message) {
                toastr[type || 'success'](message);
            }
        }

        function parsePrices(raw) {
            if (!raw) {
                return {};
            }
            if (typeof raw === 'object') {
                return raw;
            }
            try {
                return JSON.parse(raw);
            } catch (e) {
                return {};
            }
        }

        function resolveCourseType(btn) {
            var form = btn.closest('form');
            if (form) {
                var checked = form.querySelector('input[name="course_type"]:checked');
                if (checked) {
                    return checked.value;
                }
            }

            var selected = document.querySelector('[id^="flag_type_select_"][id$="_' + btn.dataset.course_id + '"].selected-course');
            if (selected && selected.dataset.courseType) {
                return selected.dataset.courseType;
            }

            var singleType = btn.getAttribute('data-single-type');
            if (singleType) {
                return singleType;
            }

            return 'online';
        }

        function resolvePrice(btn, courseType) {
            var prices = parsePrices(btn.dataset.prices);
            if (prices[courseType] !== undefined) {
                return parseFloat(prices[courseType]) || 0;
            }
            return parseFloat(btn.dataset.price || 0) || 0;
        }

        function resolveCourseCard(btn) {
            return btn.closest('.atelier-program, .trainingCard, .tooltip-coursecard, .gigs-grid, .gigs-card-slider, .gigs-card-cat, .card-tooltip-overlay, .modal-content, .catalog-card, .diploma-card');
        }

        function resolveCourseTitle(btn) {
            var card = resolveCourseCard(btn);
            if (!card) {
                return null;
            }
            var titleLink = card.querySelector('.gigs-title h3 a, .gigs-title a, h3 a');
            return titleLink ? titleLink.textContent.trim() : null;
        }

        function resolveCourseImage(btn) {
            var card = resolveCourseCard(btn);
            if (!card) {
                return null;
            }
            var img = card.querySelector('.gigs-img img');
            return img ? img.getAttribute('src') : null;
        }

        function resolveCourseSlug(btn) {
            var card = resolveCourseCard(btn);
            if (!card) {
                return null;
            }
            var link = card.querySelector('.gigs-img a, .gigs-title a');
            if (!link) {
                return null;
            }
            var href = link.getAttribute('href') || '';
            return href.replace(/^\.\//, '');
        }

        function setCartButtonState(btn, inCart) {
            btn.dataset.in_cart = inCart ? 'true' : 'false';
            var textEl = btn.querySelector('.btn-text');
            if (textEl) {
                textEl.childNodes[0].textContent = inCart ? ' إزالة من العربة ' : ' إضافة للعربة ';
            }
        }

        function setWishlistButtonState(btn, inWishlist) {
            btn.classList.toggle('active', inWishlist);
            btn.classList.toggle('favourite', inWishlist);
            var heart = btn.querySelector('.heart-icon');
            if (heart) {
                heart.classList.toggle('active', inWishlist);
                heart.classList.toggle('icon-heart-style', inWishlist);
            }
        }

        function wishlistButtonsForCourse(courseId) {
            return document.querySelectorAll('.makeWishlist[data-course_id="' + courseId + '"]');
        }

        function setAllWishlistButtonsForCourse(courseId, inWishlist) {
            wishlistButtonsForCourse(courseId).forEach(function (btn) {
                setWishlistButtonState(btn, inWishlist);
            });
        }

        function setWishlistButtonsDisabled(courseId, disabled) {
            wishlistButtonsForCourse(courseId).forEach(function (btn) {
                btn.classList.toggle('disabled', disabled);
                btn.disabled = disabled;
            });
        }

        function postJson(url, body) {
            var formData = new FormData();
            Object.keys(body).forEach(function (key) {
                if (body[key] !== null && body[key] !== undefined) {
                    formData.append(key, body[key]);
                }
            });

            return fetch(url, {
                method: 'POST',
                headers: csrfHeaders(),
                body: formData,
                credentials: 'same-origin',
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw data;
                    }
                    return data;
                });
            });
        }

        function handleCartClick(btn) {
            var courseId = btn.dataset.course_id;
            if (!courseId) {
                return;
            }

            var courseType = resolveCourseType(btn);
            var form = btn.closest('form');
            var trainingInput = form ? form.querySelector('[name="training_id"]') : null;

            btn.disabled = true;

            postJson(window.domainCommerce.cartAddUrl, {
                course_id: courseId,
                course_type: courseType,
                training_id: trainingInput ? trainingInput.value : null,
                course_title: resolveCourseTitle(btn),
                course_image: resolveCourseImage(btn),
                course_slug: resolveCourseSlug(btn),
                price: resolvePrice(btn, courseType),
            }).then(function (data) {
                updateCounts(data);
                setCartButtonState(btn, !!data.in_cart);
                showToast(data.message, data.action === 'removed' ? 'info' : 'success');
            }).catch(function (err) {
                var message = (err && err.message) || 'تعذر تحديث السلة';
                showToast(message, 'error');
            }).finally(function () {
                btn.disabled = false;
            });
        }

        function handleWishlistClick(btn) {
            var courseId = btn.dataset.course_id;
            if (!courseId) {
                return;
            }

            setWishlistButtonsDisabled(courseId, true);

            postJson(window.domainCommerce.wishlistToggleUrl, {
                course_id: courseId,
                course_title: resolveCourseTitle(btn),
                course_image: resolveCourseImage(btn),
                course_slug: resolveCourseSlug(btn),
            }).then(function (data) {
                updateCounts(data);
                setAllWishlistButtonsForCourse(courseId, !!data.in_wishlist);
                showToast(data.message, data.action === 'removed' ? 'info' : 'success');
            }).catch(function () {
                showToast('تعذر تحديث المفضلة', 'error');
            }).finally(function () {
                setWishlistButtonsDisabled(courseId, false);
            });
        }

        document.addEventListener('click', function (event) {
            var cartBtn = event.target.closest('.Add-to-Cart, .makeCartlist, #makeCartlist');
            if (cartBtn) {
                event.preventDefault();
                handleCartClick(cartBtn);
                return;
            }

            var wishBtn = event.target.closest('.makeWishlist');
            if (wishBtn) {
                event.preventDefault();
                handleWishlistClick(wishBtn);
            }
        });

        document.addEventListener('livewire:init', function () {
            Livewire.on('commerce-counts-updated', function (payload) {
                updateCounts(payload || {});
            });
        });

        if (typeof toastr !== 'undefined') {
            toastr.options = Object.assign({
                closeButton: true,
                newestOnTop: true,
                progressBar: false,
                positionClass: 'toast-top-center',
                preventDuplicates: false,
                showDuration: '300',
                hideDuration: '1000',
                timeOut: '5000',
            }, toastr.options || {});
        }
    })();
</script>
