@push('head')
    <script src="https://js.stripe.com/v3/"></script>
@endpush
<label class="mt-3" for="cardElement">
    Card Details:
</label>

<div id="cardElement">
    
</div>

<small class="form-text text-muted" id="cardErrors" role="alert"></small>

{{-- to store the token as the stripe requires token that is developed from the form details to autheticate the payment --}}
<input type="hidden" name="payment_method" id="paymentMethod">

@push('scripts')    
    <script>
        // this code below uses the Stripe function linked above to create a form in cardElement Id div above
        var stripe      = Stripe('{{ config('services.stripe.key') }}');
        var elements    = stripe.elements( { locale: 'en'} );
        var cardElement = elements.create('card');
        cardElement.mount('#cardElement');
    </script>

    <script>
        var form        = document.getElementById('paymentForm');
        var payButton   = document.getElementById('payButton');

        payButton.addEventListener('click', async(e) => {
            if(form.elements.payment_platform.value == "{{ $paymentPlatform['name'] }}")
            {
                e.preventDefault();

                const { paymentMethod, error } = await stripe.createPaymentMethod(
                    'card', cardElement, {
                        billing_details: {
                            "name": form.elements.first_name.value + " " + form.elements.last_name.value,
                            "email": form.elements.email.value 
                        }
                    }
                );

                // at this point either we have obtained the token i.e paymentMethod or the error
                // if we obtained the error
                if(error){
                    var displayError            = document.getElementById('cardErrors');
                    displayError.textContent    = error.message;
                }else{
                    // if the token gets returned as response i.e paymentMethod
                    var tokenInput              = document.getElementById('paymentMethod');
                    // store the response that is paymentMethod in it its id in the input field
                    tokenInput.value            = paymentMethod.id;
                    // submit the form and form is the variable is created
                    form.submit();
                }
            }
        });
    </script>
@endpush
