// to get current year
function getYear() {
    var yearElement = document.querySelector('.current_year');
    if (yearElement) {  // Only set if element exists
        yearElement.innerHTML = new Date().getFullYear();
    }
}

window.addEventListener('load', getYear);


// isotope js
$(window).on('load', function () {
    $('.filters_menu li').click(function () {
        $('.filters_menu li').removeClass('active');
        $(this).addClass('active');

        var data = $(this).attr('data-filter');
        $grid.isotope({
            filter: data
        })
    });

    var $grid = $(".grid").isotope({
        itemSelector: ".all",
        percentPosition: false,
        masonry: {
            columnWidth: ".all"
        }
    })
});

// nice select
$(document).ready(function() {
    // Initialize nice select
    $('select').niceSelect();

    // Initialize Bootstrap dropdowns
    $('.dropdown-toggle').dropdown();
});

/** google_map js **/
function myMap() {
    var mapProp = {
        center: new google.maps.LatLng(40.712775, -74.005973),
        zoom: 18,
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
}

// Initialize review carousel when document is ready
$(document).ready(function() {
    // Wait for reviews to be loaded
    setTimeout(function() {
        var $carousel = $(".client_owl-carousel");
        var reviewCount = $carousel.find('.item').length;

        // Destroy existing carousel if it exists
        if ($carousel.hasClass('owl-loaded')) {
            $carousel.trigger('destroy.owl.carousel');
        }

        // Initialize carousel with proper configuration
        $carousel.owlCarousel({
            loop: reviewCount > 1,
            margin: 20,
            dots: true,
            nav: false,
            autoplay: reviewCount > 1,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: reviewCount > 1 ? 2 : 1
                },
                1000: {
                    items: reviewCount > 1 ? 2 : 1
                }
            }
        });

        // Add manual navigation if there are multiple reviews
        if (reviewCount > 1) {
            // Handle navigation button clicks
            $('.prev-review').click(function() {
                $carousel.trigger('prev.owl.carousel');
            });

            $('.next-review').click(function() {
                $carousel.trigger('next.owl.carousel');
            });
        }

        // Force refresh after initialization
        setTimeout(function() {
            $carousel.trigger('refresh.owl.carousel');
        }, 100);
    }, 500); // Wait for reviews to be loaded
});