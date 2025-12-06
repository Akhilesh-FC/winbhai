<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Login</title>
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<style>
    /* General mobile layout improvements */
    @media only screen and (max-width: 768px) {
        .auth-box {
            width: 90% !important;
            padding: 20px !important;
        }

        .input-group .form-control-lg {
            font-size: 16px;
            height: 50px;
        }

        .btn {
            font-size: 16px !important;
            padding: 10px 20px !important;
        }

        .auth-wrapper {
            padding: 10px;
        }

        h3 {
            font-size: 24px !important;
        }

        .input-group-text {
            font-size: 18px;
            padding: 8px 12px;
        }

        .form-group .btn {
            width: 100%;
            margin-bottom: 10px;
        }

        .float-right {
            float: none !important;
        }

        .btn-info, .btn-success {
            width: 100% !important;
        }
    }
    
   
    body {
        background: linear-gradient(135deg, #1d1d1d, #2c3e50);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .auth-wrapper {
        min-height: 100vh;
        padding: 20px;
        background: rgba(0, 0, 0, 0.5);
    }

    .auth-box {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .input-group-text {
        background-color: rgba(255, 255, 255, 0.1) !important;
        border: none;
    }

    .form-control {
        background-color: rgba(255, 255, 255, 0.05);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    h3 {
        font-weight: 600;
        color: #ffffff;
    }

    .btn-success {
        background: #28a745;
        border: none;
        transition: 0.3s ease-in-out;
        width: 150px;
    }

    .btn-success:hover {
        background: #218838;
        transform: scale(1.05);
    }

    .alert-danger {
        font-size: 14px;
        padding: 5px 10px;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .auth-box {
            width: 95% !important;
            padding: 20px !important;
        }

        h3 {
            font-size: 22px;
        }

        .btn-success {
            width: 100%;
        }
    }
 

</style>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark">
            <div class="auth-box bg-dark border-top border-secondary">
                <div id="loginform">
                    <div class="text-center p-t-20 p-b-20">
                        <img src="assets/images/kj_logo.png"alt="homepage" class="light-logo" height="60px;" width="70px;">
                        <!--<h3 style="color:white;">ADMIN</h3>-->
                        {{-- <span class="db"><img src="assets/images/logo.png" alt="logo" /></span> --}}
                    </div>
                    <!-- Form -->
                    <form class="form-horizontal m-t-20" id="loginform" action="{{route('AuthLogin')}}" method="post">
                        @csrf
                        <div class="row p-b-30">
                            <div class="col-12">

                                @error('username')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white" id="basic-addon1">
                                            <!--<i class="ti-user"></i>-->
                                            <i class="fas fa-user" style="font-size: 30px; color: #1e90ff;"></i></span>
                                    </div>
                                    <input type="username" class="form-control form-control-lg" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" required="">

                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-warning text-white" id="basic-addon2">
                                            <!--<i class="ti-pencil"></i>-->
                                                <i class="fas fa-lock" style="font-size: 30px;color: #4caf50;"></i> 
                                            </span>
                                    </div>
                                    <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" id="password" required="">
                                      <div class="input-group-append">
                                 <!--<span class="input-group-text bg-light" onclick="togglePassword()" style="cursor: pointer;">-->
            <!--<i class="fas fa-eye" id="toggleIcon" style="color: #4caf50;"></i>-->
        </span>
    </div>
                                    @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row border-top border-secondary">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="p-t-20 d-flex justify-content-center">
                                        <!--<button class="btn btn-info" id="to-recover" type="button"><i class="fa fa-lock m-r-5"></i> Lost password?</button>-->
                                        <center><button class="btn btn-success float-right" type="submit">Login</button></center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="recoverform">
                    <div class="text-center">
                        <span class="text-white">Enter your e-mail address below and we will send you instructions how to recover a password.</span>
                    </div>
                    <div class="row m-t-20">
                        <!-- Form -->
                        <form class="col-12" action="">
                            <!-- email -->
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-danger text-white" id="basic-addon1"><i class="ti-email"></i></span>
                                </div>
                                <input type="text" class="form-control form-control-lg" placeholder="Email Address" aria-label="Username" aria-describedby="basic-addon1">
                            </div>
                            <!-- pwd -->
                            <div class="row m-t-20 p-t-20 border-top border-secondary">
                                <div class="col-12">
                                    <a class="btn btn-success" href="#" id="to-login" name="action">Back To Login</a>
                                    <button class="btn btn-info float-right" type="button" name="action">Recover</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>

    $('[data-toggle="tooltip"]').tooltip();
    $(".preloader").fadeOut();
    // ==============================================================
    // Login and Recover Password
    // ==============================================================
    $('#to-recover').on("click", function() {
        $("#loginform").slideUp();
        $("#recoverform").fadeIn();
    });
    $('#to-login').click(function(){

        $("#recoverform").hide();
        $("#loginform").fadeIn();
    });
    </script>
    
    <script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");

        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);

        // Toggle eye / eye-slash icon
        toggleIcon.classList.toggle("fa-eye");
        toggleIcon.classList.toggle("fa-eye-slash");
    }
</script>

</body>

</html>
