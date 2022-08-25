<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .g-recaptcha-outer {
            margin: 21px 0;
            text-align: center;
        }

        .g-recaptcha {
            display: inline-block;
        }

        button.stripe-button-el,
        button.stripe-button-el>span {
            background-color: red !important;
            background-image: none;
        }

        body {
            background: url("{{ url('images/background-hausing.jpg') }}") no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            background-size: cover;
            -o-background-size: cover;
            font-family: 'Open Sans', sans-serif;
        }

        .navbar {
            margin: 0 !important;
            padding: 0 !important;
        }

        input {
            -webkit-writing-mode: horizontal-tb !important;
            text-rendering: auto;
            color: initial;
            letter-spacing: normal;
            word-spacing: normal;
            text-transform: none;
            text-indent: 0px;
            text-shadow: none;
            display: inline-block;
            text-align: start;
            -webkit-appearance: textfield;
            background-color: white;
            -webkit-rtl-ordering: logical;
            cursor: text;
            margin: 0em;
            font: 400 13.3333px Arial;
            padding: 1px 0px;
            border-width: 2px;
            border-style: inset;
            border-color: initial;
            border-image: initial;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
        }

        .texto-1 {
            font-size: 17px;
            font-weight: 500;
        }

        input[type=text] {
            width: 100%;
            padding: 24px;
            border: 1px solid rgba(92, 204, 118, 0.9);
            border-radius: 4px;
            resize: vertical;
        }

        input[type=button] {
            width: 100%;
            border: 0px solid #ccc;
            border-radius: 0px;
            padding: 10px;
            font-weight: 600;
        }

        .alert {
            font-size: 13px;
        }
    </style>
    <title>Módulo de pagos - Hausing renta mzt</title>
</head>

<body>
    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light static-top mb-1 shadow">

            <div style="background-color:#5ccc76; width:100%;padding:0px; margin:0px;">
                <div class="container">
                    <a class="navbar-brand" href="#" style="color:#fff;">Módulo de Pagos</a>
                </div>

            </div>
        </nav>
        <!-- Page Content -->
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
                    <div class="card border-0 shadow my-5" style="background-color: rgba(255, 255, 255, 0.9);">
                        <div class="card-body p-5">
                            <div class="text-center pb-3">
                                <img style="max-width:100%;" src="{{ url('images/logo-hausing-mzt.jpg') }}"
                                    class="rounded" alt="...">
                            </div>
                            <h1>Hausing - Renta de departamentos Mazatlán</h1>
                            <p class="texto-1">Para su mayor comodidad, ya puede realizar los pagos de manera directa y
                                segura.</p>
                            @if (session()->has('message_error'))
                                <div class="alert alert-danger disappear">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                    <strong>{{ session()->get('message_error') }}</strong>
                                </div>
                                @endif @if (session()->has('message_exito'))
                                    <div class="alert alert-success disappear">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                        <strong>{{ session()->get('message_exito') }}</strong>
                                    </div>
                                @endif
                                <form id="myForm" action="/pago" method="POST">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <input maxlength="6" required type="text" class="form-control"
                                            id="amountInDollars" name="amountInDollars"
                                            placeholder="Cantidad a pagar ($):">
                                    </div>
                                    <div class="form-group">
                                        <input maxlength="85" required type="text" class="form-control"
                                            id="txtNombre" name="txtNombre" placeholder="Ingrese su nombre:">
                                    </div>

                                    <input type="hidden" id="stripeToken" name="stripeToken" />
                                    <input type="hidden" id="stripeEmail" name="stripeEmail" />
                                    <input type="hidden" id="amountInCents" name="amountInCents" />
                                    <div class="alert alert-danger error-check d-none">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                        <strong>Verifique la casilla "No soy un robot."</strong>
                                    </div>
                                    <div class="g-recaptcha-outer">
                                        <div class="g-recaptcha" data-sitekey="6LfcOqchAAAAANlcwGgBl__-Uh6LG0yuzbO_3GzX"
                                            style="transform:scale(0.77);-webkit-transform:scale(.9);transform-origin:0 0;-webkit-transform-origin:0 0;">
                                        </div>
                                    </div>
                                </form>
                                <center-block>
                                    <input class="btn btn-success" type="button" id="customButton"
                                        value="Pagar Reservación">
                                </center-block>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
    <script src="https://checkout.stripe.com/checkout.js"></script>
    <script>
        var handler = StripeCheckout.configure({
            key: "{{ config('services.stripe.key') }}",
            image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
            token: function(token) {
                $("#stripeToken").val(token.id);
                $("#stripeEmail").val(token.email);
                $("#amountInCents").val(Math.floor($("#amountInDollars").val() * 100));
                $("#myForm").submit();
            }
        });

        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }

        $('#customButton').on('click', function(e) {
            if ($.trim($('#amountInDollars').val()) != "" && $.trim($('#txtNombre').val()) != "") {
                if ($.isNumeric($("#amountInDollars").val())) {
                    if ($("#amountInDollars").val() > 0) {
                        var amountInCents = Math.floor($("#amountInDollars").val() * 100);
                        var displayAmount = parseFloat(Math.floor($("#amountInDollars").val() * 100) / 100).toFixed(
                            2);
                        //Checamos en captcha
                        $.ajax({
                            url: '/check',
                            type: "post",
                            data: "_token=" + $("meta[name='csrf-token']").attr("content") +
                                "&g-recaptcha-response=" + grecaptcha.getResponse(),
                            success: function(data) {
                                if (data == 1) {
                                    // Open Checkout with further options
                                    handler.open({
                                        name: 'Renta de departamentos',
                                        description: ' Monto ($' + formatNumber(displayAmount) +
                                            ') Pesos MXN.',
                                        locale: 'es',
                                        amount: amountInCents,
                                        currency: 'mxn'
                                    });
                                } else {
                                    $(".g-recaptcha-outer").effect("shake", {
                                        times: 4
                                    }, 1000);

                                }
                            }
                        });
                    } else {
                        $("#amountInDollars").val('');
                        $("#amountInDollars").focus();
                    }
                } else {
                    $("#amountInDollars").val('');
                    $("#amountInDollars").focus();
                }
            } else {
                if (!$.trim($('#amountInDollars').val())) {
                    $('#amountInDollars').val('');
                    $('#amountInDollars').focus();
                    return false;
                }
                if (!$.trim($('#txtNombre').val())) {
                    $('#txtNombre').val('');
                    $('#txtNombre').focus();
                }
            }
            e.preventDefault();
        });
        $('#amountInDollars').keypress(function(event) {
            if (event.keyCode === 10 || event.keyCode === 13) {
                event.preventDefault();
            }
        });

        // Close Checkout on page navigation
        $(window).on('popstate', function() {
            handler.close();
        });
    </script>
</body>

</html>
