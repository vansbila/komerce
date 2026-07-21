<h2><?php echo $text_instruction; ?></h2>
<div class="well text-center" style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:8px; max-width:400px; margin:0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
  <div style="background:#111827; color:#fff; padding:10px; border-radius:6px 6px 0 0; font-weight:bold; letter-spacing:1px; font-size:16px;">
    QRIS
  </div>
  <div style="padding:15px; border:1px solid #ddd; border-top:none; background:#f9fafb; border-radius:0 0 6px 6px;">
    <h4 style="margin-top:0; font-weight:bold; color:#333; font-size:15px;"><?php echo $merchant_name; ?></h4>
    <p style="font-family:monospace; font-size:11px; color:#666; margin-bottom:15px;">NMID: <?php echo $nmid; ?></p>
    
    <div style="margin:20px auto; width:180px; height:180px; border:1px solid #ccc; padding:10px; background:#fff; position:relative;">
      <!-- Grid pattern representing QR -->
      <div style="width:100%; height:100%; background: radial-gradient(#1e293b 30%, transparent 30%) 0 0, radial-gradient(#1e293b 30%, transparent 30%) 4px 4px; background-size: 8px 8px; opacity:0.85;"></div>
      <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:5px 10px; border:1px solid #ddd; font-weight:bold; font-size:11px; color:red; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.1);">QRIS</div>
    </div>
    
    <div style="margin-top:15px; padding-top:10px; border-top:1px solid #eee;">
      <span style="font-size:11px; color:#666; text-transform:uppercase; font-weight:bold; display:block;">Total Pembayaran</span>
      <h3 style="margin:5px 0; color:#2563eb; font-weight:extrabold; font-size:20px;"><?php echo $amount; ?></h3>
    </div>
  </div>
  <p style="font-size:11px; color:#666; margin-top:15px; line-height:1.4; text-align:center;"><?php echo $text_scan; ?></p>
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
        url: 'index.php?route=extension/payment/rajaongkir_qris/confirm',
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