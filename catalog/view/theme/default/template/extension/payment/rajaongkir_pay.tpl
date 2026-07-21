<h2><?php echo $text_instruction; ?></h2>
<div class="well well-sm">
  <p><strong><?php echo $text_payable; ?></strong></p>
  <p><?php echo $bank_details; ?></p>
  <p><?php echo $text_payment; ?></p>
</div>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="Loading..." />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
    $.ajax({
        type: 'get',
        url: 'index.php?route=extension/payment/rajaongkir_pay/confirm',
        cache: false,
        beforeSend: function() {
            $('#button-confirm').button('loading');
        },
        complete: function() {
            $('#button-confirm').button('reset');
        },
        success: function() {
            location = '<?php echo $continue; ?>';
        }
    });
});
//--></script>