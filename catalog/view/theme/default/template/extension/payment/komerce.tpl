<div class="payment-komerce-container" style="border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; background: #fff; margin-bottom: 20px;">
  <h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #1e293b;"><i class="fa fa-shield"></i> Pilih Metode Pembayaran Instan</h4>
  <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Proses pembayaran aman dan otomatis terverifikasi secara real-time via API Komerce.</p>

  <div class="payment-options-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-bottom: 20px;">
    
    <?php if ($qris_enabled) { ?>
    <label class="payment-card-label" style="cursor: pointer; position: relative; border: 2px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; display: block; transition: all 0.2s;">
      <input type="radio" name="komerce_select_method" value="qris" checked="checked" style="position: absolute; opacity: 0;" />
      <div class="payment-icon" style="height: 35px; line-height: 35px; margin-bottom: 8px;">
         <strong style="color: #ef4444; font-size: 16px;">QRIS</strong>
      </div>
      <span style="font-size: 12px; font-weight: 500; display: block;">QRIS Scan</span>
    </label>
    <?php } ?>

    <?php if ($bca_va_enabled) { ?>
    <label class="payment-card-label" style="cursor: pointer; position: relative; border: 2px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; display: block; transition: all 0.2s;">
      <input type="radio" name="komerce_select_method" value="bca_va" style="position: absolute; opacity: 0;" />
      <div class="payment-icon" style="height: 35px; line-height: 35px; margin-bottom: 8px;">
         <strong style="color: #2563eb; font-size: 15px;">BCA VA</strong>
      </div>
      <span style="font-size: 12px; font-weight: 500; display: block;">BCA VA</span>
    </label>
    <?php } ?>

    <?php if ($mandiri_va_enabled) { ?>
    <label class="payment-card-label" style="cursor: pointer; position: relative; border: 2px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; display: block; transition: all 0.2s;">
      <input type="radio" name="komerce_select_method" value="mandiri_va" style="position: absolute; opacity: 0;" />
      <div class="payment-icon" style="height: 35px; line-height: 35px; margin-bottom: 8px;">
         <strong style="color: #d97706; font-size: 14px;">Mandiri</strong>
      </div>
      <span style="font-size: 12px; font-weight: 500; display: block;">Mandiri VA</span>
    </label>
    <?php } ?>

    <?php if ($bni_va_enabled) { ?>
    <label class="payment-card-label" style="cursor: pointer; position: relative; border: 2px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; display: block; transition: all 0.2s;">
      <input type="radio" name="komerce_select_method" value="bni_va" style="position: absolute; opacity: 0;" />
      <div class="payment-icon" style="height: 35px; line-height: 35px; margin-bottom: 8px;">
         <strong style="color: #0d9488; font-size: 14px;">BNI VA</strong>
      </div>
      <span style="font-size: 12px; font-weight: 500; display: block;">BNI VA</span>
    </label>
    <?php } ?>

    <?php if ($bri_va_enabled) { ?>
    <label class="payment-card-label" style="cursor: pointer; position: relative; border: 2px solid #cbd5e1; padding: 12px; border-radius: 6px; text-align: center; display: block; transition: all 0.2s;">
      <input type="radio" name="komerce_select_method" value="bri_va" style="position: absolute; opacity: 0;" />
      <div class="payment-icon" style="height: 35px; line-height: 35px; margin-bottom: 8px;">
         <strong style="color: #0284c7; font-size: 14px;">BRI VA</strong>
      </div>
      <span style="font-size: 12px; font-weight: 500; display: block;">BRI VA</span>
    </label>
    <?php } ?>

  </div>

  <div class="buttons" style="margin: 0; text-align: right;">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary btn-lg" style="background: #10b981; border: none; padding: 12px 28px; font-size: 15px; border-radius: 6px; font-weight: 600; transition: background 0.2s; color: #fff;" />
  </div>
</div>

<script type="text/javascript"><!--
// Handle dynamic visual borders on selecting payment method card
$('.payment-card-label').on('click', function() {
    $('.payment-card-label').css({'border-color': '#cbd5e1', 'background-color': '#fff'});
    $(this).css({'border-color': '#10b981', 'background-color': '#f0fdf4'});
    $(this).find('input[type="radio"]').prop('checked', true);
});

// Trigger confirmed click and connect with API backend Controller
$('#button-confirm').on('click', function() {
    var selected_method = $('input[name="komerce_select_method"]:checked').val();
    
    $.ajax({
        url: '<?php echo $action_checkout; ?>',
        type: 'post',
        data: { payment_method: selected_method },
        dataType: 'json',
        beforeSend: function() {
            $('#button-confirm').button('loading');
        },
        complete: function() {
            $('#button-confirm').button('reset');
        },
        success: function(json) {
            if (json['success'] && json['redirect']) {
                location = json['redirect'];
            } else if (json['error']) {
                alert(json['error']);
            } else if (json['message']) {
                alert(json['message']);
            } else {
                alert('Terjadi kesalahan yang tidak terduga.');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});
//--></script>
