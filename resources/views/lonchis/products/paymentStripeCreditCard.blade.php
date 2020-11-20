


<form  class="form-horizontal" role="form" name="myForm2" id="cc-form">
    <input type="hidden" name="_token" value="{{ csrf_token()}}">


    <div class="payment-errors">

    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">CC</label>
        <div class="col-md-6">
            <input type="text" class="form-control card-number" name="cc_number" value="4242424242424242" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">CVC</label>
        <div class="col-md-6">
            <input type="text" class="form-control card-cvc" name="cc_security_code" value="123" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Zip Code</label>
        <div class="col-md-6">
            <input type="text" class="form-control address_zip" name="address_zip" value="33133" >
        </div>
    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Expiration Date</label>
        <div class="col-md-6">

            <label class="col-md-1 control-label">Year</label>
            <div class="col-md-2">
                <select name="Year" class="card-expiry-year">
                    <option value="">Year</option>
                    <script language="javascript">
                        for (var i = 2017; i < 2030; i++) {
                            document.write("<option value=\"" + i + "\">" + i + "</option>\n");
                        }
                    </script>
                </select>
            </div>
            <label class="col-md-1 control-label">Mes</label>
            <div class="col-md-2">
                <select name="month" class="card-expiry-month">
                    <option value="">Month</option>
                    <script language="javascript">
                        for (var i = 1; i < 13; i++) {
                            document.write("<option value=\"" + i + "\">" + i + "</option>\n");
                        }
                    </script>
                </select>
            </div>
        </div>

    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">Save</button>

        </div>
    </div>
</form>
<script type="text/javascript">
    //Stripe.setPublishableKey('pk_test_6pRNASCoBOKtIshFeQd4XMUh');

    $("#cc-form").submit(function (e) {
        e.preventDefault();
        $(this).find('button').prop('disabled', true); // Disable submission
        Stripe.source.create({
            type: 'card',
            card: {
                number: $('.card-number').val(),
                cvc: $('.card-cvc').val(),
                exp_month: $('.card-expiry-month').val(),
                exp_year: $('.card-expiry-year').val(),
            },
            owner: {
                address: {
                    postal_code: $('.address_zip').val()
                }
            }
        }, stripeResponseHandler);
    });


    function stripeResponseHandler(status, response) {

        // Grab the form:
        var $form = $('#cc-form');

        if (response.error) { // Problem!

            // Show the errors on the form
            $form.find('.payment-errors').text(response.error.message);
            $form.find('button').prop('disabled', false); // Re-enable submission

        } else { // Source was created!

            // Get the source ID:
            console.log("Token: " + response.id);
            var source = response.id;
            var data = {
                "source":source
            };
            $.post("/stripe/source/card",data, function (data) {
                $(".result").html(data);
            });

        }
    }
</script>