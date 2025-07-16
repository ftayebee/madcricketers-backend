<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Scoreboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background-color: #f4fffd;
            box-shadow: 0 0 5px 0px #33333326;
        }

        .navbar-brand,
        .nav-link {
            color: #222222 !important;
        }

        .swiper {
            width: 100%;
            height: 300px;
        }

        .swiper-slide {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #00bfa5;
            display: inline-block;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    @include('frontend.includes.navbar')

    <!-- Recent Matches Slider -->
    <div class="container mt-4">
        <div class="section-title">Recent Matches</div>
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">India vs Pakistan<br>India won by 5 wickets</div>
                <div class="swiper-slide">Australia vs England<br>England won by 20 runs</div>
                <div class="swiper-slide">South Africa vs Bangladesh<br>Match tied</div>
                <div class="swiper-slide">South Africa vs Bangladesh<br>Match tied</div>
            </div>
        </div>
    </div>

    <!-- Upcoming Matches -->
    <div class="container mt-5">
        <div class="section-title">Upcoming Matches</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>India vs Australia</h5>
                    <p>Date: July 20, 2025</p>
                    <p>Time: 7:00 PM</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>Bangladesh vs New Zealand</h5>
                    <p>Date: July 21, 2025</p>
                    <p>Time: 4:00 PM</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h5>Pakistan vs Sri Lanka</h5>
                    <p>Date: July 22, 2025</p>
                    <p>Time: 6:30 PM</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Players -->
    <div class="container mt-5">
        <div class="section-title">Top Players</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Most Runs</h6>
                    <p>Virat Kohli - 562 Runs</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Most Wickets</h6>
                    <p>Shaheen Afridi - 24 Wickets</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Best Economy</h6>
                    <p>Trent Boult - 3.2 ER</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                }
            }
        });
    </script>
</body>

</html>
