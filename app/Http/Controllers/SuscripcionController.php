<?php

namespace App\Http\Controllers;

use Throwable;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SuscripcionController extends Controller
{
    public function check()
    {
        if ($_POST['g-recaptcha-response']) {
            $captcha = $_POST['g-recaptcha-response'];
            $secret = env('CAPTCHA_SECRET_KEY');
            $json = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $captcha), true);
            if ($json['success']) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return -1;
        }
    }


    public function pago(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $customer = Customer::create(array(
                'email' => $request->stripeEmail,
                'source' => $request->stripeToken
            ));
            $charge = Charge::create(array(
                'customer' => $customer->id,
                'amount' => ($request->amountInCents),
                'currency' => 'mxn',
                'description' => "Pago por concepto de renta de departamento: " . ($request->txtNombre),
                'receipt_email' => $request->stripeEmail,
            ));
            return redirect('/')->with('message_exito', 'Pago exitoso. Se envió el informe del pago al correo proporcionado.');
        } catch (\Exception $ex) {
            //manejando las posbiles excepciones
            $error = 'Ocurrió un error, por favor verifique sus datos.';
            //saldo insuficiente
            //dd($ex);
            try {
                if ($ex->getDeclineCode() == "authentication_required") {
                    //The customer should try again and authenticate their card when prompted during the transaction.
                    $error = "Tarjeta rechazada, esta transacción requiere de autenticación.";
                } else if ($ex->getDeclineCode() == "approve_with_id") {
                    //The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.
                    $error = "Pago no autorizado, intente de nuevo o pónganse en contacto con su banco.";
                } else if ($ex->getDeclineCode() == "call_issuer") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Pago no autorizado por su banco, por favor póngase en contacto con ellos.";
                } else if ($ex->getDeclineCode() == "card_not_supported") {
                    //The customer needs to contact their card issuer to make sure their card can be used to make this type of purchase.
                    $error = "Su tarjeta no soporta este tipo de transacciones, por favor contacte a su banco.";
                } else if ($ex->getDeclineCode() == "card_velocity_exceeded") {
                    //The customer should contact their card issuer for more information.
                    $error = "Su tarjeta a pasado el límite de crédito disponible, por favor contacte a su banco.";
                } else if ($ex->getDeclineCode() == "currency_not_supported") {
                    //The customer needs to check with the issuer whether the card can be used for the type of currency specified.
                    $error = "La tarjeta no soporta transacciones con este tipo de moneda(PESOS MXN).";
                } else if ($ex->getDeclineCode() == "do_not_honor") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Tarjeta rechazada por indicaciones del banco, por favor contacte a su banco.";
                } else if ($ex->getDeclineCode() == "do_not_try_again") {
                    //The customer should contact their card issuer for more information.
                    $error = "Tarjeta rechazada por indicaciones del banco, por favor no reintente y contacte a su banco.";
                } else if ($ex->getDeclineCode() == "duplicate_transaction") {
                    //Check to see if a recent payment already exists.
                    $error = "Error, ya se ha enviado una transacción con la misma tarjeta y misma cantidad. Verifique si ya ha realizado este pago previamente.";
                } else if ($ex->getDeclineCode() == "expired_card") {
                    //The customer should use another card.
                    $error = "Tarjeta expirada, por favor utilice otra tarjeta.";
                } else if ($ex->getDeclineCode() == "fraudulent") {
                    //Do not report more detailed information to your customer. Instead, present as you would the generic_decline described below.
                    $error = "Tarjeta no aceptada, por favor utilice otra tarjeta.";
                } else if ($ex->getDeclineCode() == "generic_decline") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Tarjeta no aceptada, por favor consulte con su banco.";
                } else if ($ex->getDeclineCode() == "incorrect_number") {
                    //The customer should try again using the correct card number.
                    $error = "El número de tarjeta es incorrecto, por favor verifique sus datos y reintente.";
                } else if ($ex->getDeclineCode() == "incorrect_cvc") {
                    //The customer should try again using the correct CVC.
                    $error = "El número de CVC es incorrecto, por favor verifique sus datos y reintente.";
                } else if ($ex->getDeclineCode() == "incorrect_pin") {
                    //The customer should try again using the correct PIN.
                    $error = "Por favor verifique su número de PIN y reintente.";
                } else if ($ex->getDeclineCode() == "incorrect_zip") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Por favor verifique su número de PIN y reintente.";
                } else if ($ex->getDeclineCode() == "generic_decline") {
                    //The customer should try again using the correct billing ZIP/postal code.
                    $error = "Por favor verifique su número de ZIP y reintente.";
                } else if ($ex->getDeclineCode() == "insufficient_funds") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Tarjeta no aceptada, por favor consulte con su banco.";
                } else if ($ex->getDeclineCode() == "invalid_account") {
                    //The customer needs to contact their card issuer to check that the card is working correctly.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "invalid_amount") {
                    //If the amount appears to be correct, the customer needs to check with their card issuer that they can make purchases of that amount.
                    $error = "La cantidad se ha escrito de manera incorrecta o excede el monto permitido.";
                } else if ($ex->getDeclineCode() == "invalid_cvc") {
                    //The customer should try again using the correct CVC..
                    $error = "Por favor verifique su número de CVC y reintente.";
                } else if ($ex->getDeclineCode() == "invalid_expiry_year") {
                    //The customer should try again using the correct expiration date.
                    $error = "El año de expiración ingresado no es válido, por favor verifique y reintente";
                } else if ($ex->getDeclineCode() == "invalid_number") {
                    //The customer should try again using the correct card number.
                    $error = "El número de tarjeta ingresado es incorrecto, por favor verifique y reintente";
                } else if ($ex->getDeclineCode() == "invalid_pin") {
                    //The customer should try again using the correct PIN.
                    $error = "El número de PIN no es válido, por favor verifique y reintente";
                } else if ($ex->getDeclineCode() == "issuer_not_available") {
                    //The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.
                    $error = "No se pudo hacer la conexión con su banco, por favor intente de nuevo. si no se pudo resolver el problema, contacte a su banco.";
                } else if ($ex->getDeclineCode() == "lost_card") {
                    //The specific reason for the decline should not be reported to the customer. Instead, it needs to be presented as a generic decline.
                    $error = "Esta tarjeta cuenta con reporte de extravío, por favor contacte a su banco.";
                } else if ($ex->getDeclineCode() == "merchant_blacklist") {
                    //Do not report more detailed information to your customer. Instead, present as you would the generic_decline described above.
                    $error = "Transacción no permitida por stripe, por favor utilice otra tarjeta.";
                } else if ($ex->getDeclineCode() == "new_account_information_available") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "no_action_taken") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "not_permitted") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "processing_error") {
                    //The payment should be attempted again. If it still cannot be processed, try again later.
                    $error = "Ocurrió un error al procesar el pago, por favor reintente de nuevo.";
                } else if ($ex->getDeclineCode() == "reenter_transaction") {
                    //The payment should be attempted again. If it still cannot be processed, the customer needs to contact their card issuer.
                    $error = "Ocurrió un error al procesar el pago, por favor reintente de nuevo.";
                } else if ($ex->getDeclineCode() == "restricted_card") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "Esta tarjeta está restringida para hacer pagos, por favor verifique que no tenga reporte de extravío o robo.";
                } else if ($ex->getDeclineCode() == "revocation_of_all_authorizations") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco no autorizó esta transacción, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "revocation_of_authorization") {
                    //The customer needs to contact their card issuer for more information.
                    $error = "El banco no autorizó esta transacción, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "service_not_allowed") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco no autorizó esta transacción, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "stolen_card") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un reporte de robo en esta tarjeta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "stop_payment_order") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "testmode_decline") {
                    //A genuine card must be used to make a payment.
                    $error = "Debe ingresar una tarjeta genuina y no de pruebas.";
                } else if ($ex->getDeclineCode() == "transaction_not_allowed") {
                    //The customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "try_again_later") {
                    //Ask the customer to attempt the payment again. If subsequent payments are declined, the customer should contact their card issuer for more information.
                    $error = "El banco ha encontrado un problema con su cuenta, por favor intente de nuevo o verifiquelo con su banco.";
                } else if ($ex->getDeclineCode() == "withdrawal_count_limit_exceeded") {
                    //The customer should contact their card issuer for more information.
                    $error = "La tarjeta ha excedido el límite de crédito, por favor verifiquelo con su banco.";
                }
            } catch (\Throwable $th) {
                //error de numero
                if ($ex->getMessage() == "A non well formed numeric value encountered") {
                    $error = 'Verifique bien la cantidad por favor';
                } else {
                    $error = 'Ocurrió un error, por favor verifique sus datos y reintente.';
                }
            }


            return redirect('/')->with('message_error', $error);
        }
    }
}
