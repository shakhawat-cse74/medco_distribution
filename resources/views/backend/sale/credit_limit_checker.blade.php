<!-- Credit Limit Checker UI and Logic -->
<div id="credit-limit-error-box" class="alert alert-danger" style="display: none; margin-top: 10px;">
    <strong>{{__('db.Credit limit exceeded')}}!</strong><br>
    {{__('db.Credit Limit')}}: <span id="cl-limit-display">0.00</span><br>
    {{__('db.Existing Due')}}: <span id="cl-existing-due-display">0.00</span><br>
    {{__('db.New Due')}}: <span id="cl-new-due-display">0.00</span><br>
    {{__('db.Total Due After Sale')}}: <span id="cl-total-due-display">0.00</span>
</div>

@push('scripts')
<script>
    var creditCheckToken = 0;
    var currentXhr = null;
    
    // Safely inject original sale data for Edit scenarios
    var originalSaleData = @json(isset($lims_sale_data) ? ['customer_id' => $lims_sale_data->customer_id, 'due' => max(0, $lims_sale_data->grand_total - $lims_sale_data->paid_amount)] : null);

    function checkCreditLimit() {
        creditCheckToken++;
        var currentToken = creditCheckToken;
        
        var selectedOption = $('#customer_id option:selected');
        if (!selectedOption.length || !selectedOption.val()) {
            if (currentXhr) currentXhr.abort();
            $('#submit-btn, #submit-button').prop('disabled', false);
            $('#credit-limit-error-box').hide();
            return;
        }

        var customer_id = $('#customer_id').val();
        var credit_limit = parseFloat(selectedOption.data('credit-limit')) || 0;
        var current_grand_total = parseFloat($('input[name="grand_total"]').val()) || 0;
        
        // Sum all payments
        var total_paying = 0;
        if ($('.total_paying').length > 0) {
            total_paying = parseFloat($('.total_paying').text()) || 0;
        } else if ($('input[name="paid_amount[]"]').length > 0) {
            $('input[name="paid_amount[]"]').each(function() {
                total_paying += parseFloat($(this).val()) || 0;
            });
        } else if ($('input[name="paid_amount"]').length > 0) {
            total_paying = parseFloat($('input[name="paid_amount"]').val()) || 0;
        }
        
        var new_due = current_grand_total - total_paying;
        if (new_due < 0) new_due = 0;

        if (new_due <= 0) {
            if (currentXhr) currentXhr.abort();
            // Fully paid, always allowed regardless of credit limit
            $('#submit-btn, #submit-button').prop('disabled', false);
            $('#credit-limit-error-box').hide();
            return;
        }

        if (credit_limit <= 0) {
            if (currentXhr) currentXhr.abort();
            // No credit allowed, but they are trying to leave a balance
            $('#credit-limit-error-box strong').text('{{__("db.Credit limit exceeded!")}}');
            $('#cl-limit-display').text('0.00 (No Credit Allowed)');
            $('#cl-existing-due-display').text('-');
            $('#cl-new-due-display').text(new_due.toFixed(2));
            $('#cl-total-due-display').text('-');
            
            $('#credit-limit-error-box').show();
            $('#submit-btn, #submit-button').prop('disabled', true);
            return;
        }

        // Before sending request, disable submit controls while checking
        $('#submit-btn, #submit-button').prop('disabled', true);
        
        if (currentXhr) {
            currentXhr.abort();
        }

        currentXhr = $.ajax({
            url: '/customer/' + customer_id + '/due',
            type: 'GET'
        });

        currentXhr.done(function(existing_due_raw) {
            if (currentToken !== creditCheckToken) {
                return; // Stale request
            }

            var existing_due = parseFloat(existing_due_raw) || 0;
            
            // Adjust existing due if editing the original sale for the same customer
            if (originalSaleData && originalSaleData.customer_id == customer_id) {
                existing_due -= originalSaleData.due;
                if (existing_due < 0) existing_due = 0;
            }
            
            var total_due_after = existing_due + new_due;

            $('#credit-limit-error-box strong').text('{{__("db.Credit limit exceeded!")}}');
            
            if (total_due_after > credit_limit) {
                $('#cl-limit-display').text(credit_limit.toFixed(2));
                $('#cl-existing-due-display').text(existing_due.toFixed(2));
                $('#cl-new-due-display').text(new_due.toFixed(2));
                $('#cl-total-due-display').text(total_due_after.toFixed(2));
                
                $('#credit-limit-error-box').show();
                $('#submit-btn, #submit-button').prop('disabled', true);
            } else {
                $('#submit-btn, #submit-button').prop('disabled', false);
                $('#credit-limit-error-box').hide();
            }
        }).fail(function(xhr, status, error) {
            if (currentToken !== creditCheckToken || status === 'abort') {
                return; // Stale request or aborted
            }
            
            console.error('AJAX Error:', xhr.status, xhr.responseText);
            $('#credit-limit-error-box strong').text('Could not verify credit limit. Server error.');
            $('#cl-limit-display').text('Error');
            $('#cl-existing-due-display').text('Error');
            $('#cl-new-due-display').text(new_due.toFixed(2));
            $('#cl-total-due-display').text('Error');
            $('#credit-limit-error-box').show();
            $('#submit-btn, #submit-button').prop('disabled', true); 
        });
    }
    
    // Attach listeners
    $(document).ready(function() {
        $('#customer_id').on('change', function() { checkCreditLimit(); });
        $('input[name="grand_total"], input[name="paid_amount"], input[name="paid_amount[]"]').on('input change', function() {
            checkCreditLimit();
        });
        $(document).on('credit_limit_check_required', function() {
            checkCreditLimit();
        });
    });
</script>
@endpush
