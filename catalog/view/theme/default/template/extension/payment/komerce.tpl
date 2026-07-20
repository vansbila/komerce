<div class="payment-komerce-container" style="border: 1px solid #e2e8f0; padding: 25px; border-radius: 10px; background: #fff; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
  <div style="display: flex; align-items: center; margin-bottom: 15px;">
    <div style="background: #0070ba; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
      <i class="fa fa-shield" style="font-size: 20px;"></i>
    </div>
    <div>
      <h4 style="margin: 0; font-weight: 700; color: #1e293b;">Pembayaran Aman via RajaOngkir</h4>
      <p style="margin: 0; font-size: 13px; color: #64748b;">Mendukung QRIS, Virtual Account, dan E-Wallet.</p>
    </div>
  </div>

  <div style="background: #f8fafc; border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px dashed #cbd5e1;">
    <p style="font-size: 13px; color: #475569; margin-bottom: 10px; line-height: 1.6;">
      Setelah menekan tombol di bawah, Anda akan dialihkan ke halaman pembayaran aman <strong>RajaOngkir</strong> untuk memilih metode pembayaran:
    </p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; opacity: 0.7;">
      <img src="https://rajaongkir.com/assets/img/logo-qris.png" alt="QRIS" style="height: 20px;">
      <span style="color: #cbd5e1;">|</span>
      <strong style="font-size: 11px; color: #64748b;">BCA VA</strong>
      <strong style="font-size: 11px; color: #64748b;">MANDIRI</strong>
      <strong style="font-size: 11px; color: #64748b;">BNI</strong>
      <strong style="font-size: 11px; color: #64748b;">BRI</strong>
    </div>
  </div>

  <div class="buttons" style="margin: 0; text-align: right;">
    <button type="button" id="button-confirm" class="btn btn-primary" style="background: #0070ba; border: none; padding: 14px 35px; font-size: 16px; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 10px 15px -3px rgba(0, 112, 186, 0.3);">
      <i class="fa fa-external-link"></i> &nbsp; <?php echo $button_confirm; ?>
    </button>
  </div>
</div>

<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
    $.ajax({
        url: 'index.php?route=extension/payment/komerce/checkout',
        dataType: 'json',
        beforeSend: function() {
            $('#button-confirm').button('loading');
        },
        complete: function() {
            $('#button-confirm').button('reset');
        },
        success: function(json) {
            // Jika Controller mengembalikan JSON redirect
            if (json['redirect']) {
                location = json['redirect'];
            } else if (json['error']) {
                alert(json['error']);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            // Fallback jika controller langsung melakukan redirect header (PHP Redirect)
            // Dalam banyak kasus OC 2.3, window.location akan berubah otomatis
            window.location = 'index.php?route=extension/payment/komerce/checkout';
        }
    });
});
//--></script>
