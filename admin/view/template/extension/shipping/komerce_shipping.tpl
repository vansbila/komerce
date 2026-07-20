<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-shipping" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
        <a href="<?php echo $cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i> Batal</a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-pencil"></i> Pengaturan</h3></div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" id="form-shipping" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label">Status</label>
            <div class="col-sm-10">
              <select name="komerce_shipping_status" class="form-control">
                <option value="1" <?php echo ($komerce_shipping_status == '1' ? 'selected' : ''); ?>>Enabled</option>
                <option value="0" <?php echo ($komerce_shipping_status == '0' ? 'selected' : ''); ?>>Disabled</option>
              </select>
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label">API Key</label>
            <div class="col-sm-10">
              <input type="password" name="komerce_shipping_apikey" value="<?php echo $komerce_shipping_apikey; ?>" id="input-apikey" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">API Tier</label>
            <div class="col-sm-10">
              <select name="komerce_shipping_api_tier" class="form-control">
                <option value="starter" <?php echo ($komerce_shipping_api_tier == 'starter' ? 'selected' : ''); ?>>Starter</option>
                <option value="basic" <?php echo ($komerce_shipping_api_tier == 'basic' ? 'selected' : ''); ?>>Basic</option>
                <option value="pro" <?php echo ($komerce_shipping_api_tier == 'pro' ? 'selected' : ''); ?>>Pro (Kecamatan)</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Sync Data</label>
            <div class="col-sm-10">
              <button type="button" id="button-sync-db" class="btn btn-warning"><i class="fa fa-refresh"></i> SINKRONKAN DATA REGIONAL</button>
              <div id="sync-msg" style="margin-top:10px; display:none;"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Lokasi Toko (Origin)</label>
            <div class="col-sm-3">
              <select id="select-province" class="form-control"><option value="">-- Provinsi --</option></select>
              <input type="hidden" name="komerce_shipping_province_id" value="<?php echo $komerce_shipping_province_id; ?>" id="input-province-id" />
            </div>
            <div class="col-sm-3">
              <select id="select-city" class="form-control"><option value="">-- Kota --</option></select>
              <input type="hidden" name="komerce_shipping_city_id" value="<?php echo $komerce_shipping_city_id; ?>" id="input-city-id" />
            </div>
            <div class="col-sm-4">
              <select id="select-subdistrict" class="form-control"><option value="">-- Kecamatan --</option></select>
              <input type="hidden" name="komerce_shipping_subdistrict_id" value="<?php echo $komerce_shipping_subdistrict_id; ?>" id="input-subdistrict-id" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Urutan</label>
            <div class="col-sm-10">
              <input type="text" name="komerce_shipping_sort_order" value="<?php echo $komerce_shipping_sort_order; ?>" class="form-control" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
    function loadProvinces() {
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_provinces_ajax&token=<?php echo $token; ?>',
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Provinsi --</option>';
                var current = $('#input-province-id').val();
                $.each(json, function(i, v) { html += '<option value="'+v.province_id+'" '+(v.province_id==current?'selected':'')+'>'+v.province_name+'</option>'; });
                $('#select-province').html(html);
                if (current) loadCities(current);
            }
        });
    }
    function loadCities(province_id) {
        $('#select-city').html('<option value="">Loading...</option>');
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_cities_ajax&token=<?php echo $token; ?>&province_id=' + province_id,
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Kota --</option>';
                var current = $('#input-city-id').val();
                $.each(json, function(i, v) { html += '<option value="'+v.city_id+'" '+(v.city_id==current?'selected':'')+'>'+v.type+' '+v.city_name+'</option>'; });
                $('#select-city').html(html);
                if (current) loadSubdistricts(current);
            }
        });
    }
    function loadSubdistricts(city_id) {
        $('#select-subdistrict').html('<option value="">Loading...</option>');
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_subdistricts_ajax&token=<?php echo $token; ?>&city_id=' + city_id,
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Kecamatan --</option>';
                var current = $('#input-subdistrict-id').val();
                $.each(json, function(i, v) { html += '<option value="'+v.subdistrict_id+'" '+(v.subdistrict_id==current?'selected':'')+'>'+v.subdistrict_name+'</option>'; });
                $('#select-subdistrict').html(html);
            }
        });
    }
    $('#select-province').on('change', function() { $('#input-province-id').val($(this).val()); loadCities($(this).val()); });
    $('#select-city').on('change', function() { $('#input-city-id').val($(this).val()); loadSubdistricts($(this).val()); });
    $('#select-subdistrict').on('change', function() { $('#input-subdistrict-id').val($(this).val()); });
    loadProvinces();

    $('#button-sync-db').on('click', function() {
        if ($('#input-apikey').val() == '') { alert('Isi API Key & Klik SIMPAN dulu!'); return; }
        var btn = $(this); btn.button('loading'); $('#sync-msg').hide();
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/sync_db&token=<?php echo $token; ?>',
            dataType: 'json',
            success: function(json) {
                btn.button('reset');
                if (json['success']) { $('#sync-msg').html('<div class="alert alert-success">'+json['message']+'</div>').fadeIn(); loadProvinces(); }
                else { $('#sync-msg').html('<div class="alert alert-danger">'+json['message']+'</div>').fadeIn(); }
            },
            error: function() { btn.button('reset'); alert('Error koneksi server.'); }
        });
    });
});
//--></script>
<?php echo $footer; ?>
