<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-shipping" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
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
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> Edit Pengaturan Shipping</h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-shipping" class="form-horizontal">
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_shipping_status" id="input-status" class="form-control">
                <option value="1" <?php if ($komerce_shipping_status == '1') echo 'selected="selected"'; ?>><?php echo $text_enabled; ?></option>
                <option value="0" <?php if ($komerce_shipping_status == '0') echo 'selected="selected"'; ?>><?php echo $text_disabled; ?></option>
              </select>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-shipping-apikey">API Key Shipping Cost</label>
            <div class="col-sm-10">
              <input type="password" name="komerce_shipping_apikey" value="<?php echo $komerce_shipping_apikey; ?>" placeholder="API Key Komerce Shipping" id="input-shipping-apikey" class="form-control" />
              <span class="help-block">Dedicated API Key untuk perhitungan ongkos kirim. Jika kosong, sistem otomatis menggunakan API Key utama dari modul pembayaran.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-api-tier">Paket API RajaOngkir / Komerce</label>
            <div class="col-sm-10">
              <select name="komerce_shipping_api_tier" id="input-api-tier" class="form-control">
                <option value="starter" <?php if ($komerce_shipping_api_tier == 'starter') echo 'selected="selected"'; ?>>Starter (Hanya Provinsi & Kota/Kabupaten)</option>
                <option value="basic" <?php if ($komerce_shipping_api_tier == 'basic') echo 'selected="selected"'; ?>>Basic (Hanya Provinsi & Kota/Kabupaten, Lebih Banyak Kurir)</option>
                <option value="pro" <?php if ($komerce_shipping_api_tier == 'pro' || empty($komerce_shipping_api_tier)) echo 'selected="selected"'; ?>>Pro (Lengkap: Provinsi, Kota/Kabupaten, & Kecamatan)</option>
              </select>
              <span class="help-block">Tingkat paket akun API RajaOngkir / Komerce Anda. Jika Anda memakai yang <strong>Pro</strong>, pencarian kota, kecamatan, dan kabupaten akan muncul lengkap secara otomatis.</span>
            </div>
          </div>

          <!-- Dynamic Cascaded Region Selector Dropdowns -->
          <div class="form-group required" style="background-color: #fcfcfc; padding: 15px 0; border-top: 1px solid #f1f1f1; border-bottom: 1px solid #f1f1f1;">
            <label class="col-sm-2 control-label" style="font-weight: bold; color: #d9534f;"><i class="fa fa-map-marker"></i> Pilih Origin (Dropdown)</label>
            <div class="col-sm-3">
              <select id="select-province" class="form-control" style="border: 1px solid #f0ad4e; font-weight: bold;">
                <option value="">-- Pilih Provinsi --</option>
              </select>
              <span class="help-block" style="font-size: 11px; margin-top: 4px;">Pilih Provinsi Terlebih Dahulu</span>
            </div>
            <div class="col-sm-3">
              <select id="select-city" class="form-control" style="border: 1px solid #f0ad4e; font-weight: bold;">
                <option value="">-- Pilih Kota --</option>
              </select>
              <span class="help-block" style="font-size: 11px; margin-top: 4px;">Pilih Kota/Kabupaten</span>
            </div>
            <div class="col-sm-4">
              <select id="select-subdistrict" class="form-control" style="border: 1px solid #f0ad4e; font-weight: bold;">
                <option value="">-- Pilih Kecamatan --</option>
              </select>
              <span class="help-block" style="font-size: 11px; margin-top: 4px;">Pilih Kecamatan Terakhir</span>
            </div>
          </div>

          <!-- Readonly Saved Value Sync Fields -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-province-id">Origin Province ID & Nama</label>
            <div class="col-sm-3">
              <input type="text" name="komerce_shipping_province_id" value="<?php echo $komerce_shipping_province_id; ?>" id="input-province-id" class="form-control" placeholder="ID Provinsi" readonly />
            </div>
            <div class="col-sm-7">
              <input type="text" name="komerce_shipping_province_name" value="<?php echo $komerce_shipping_province_name; ?>" id="input-province-name" class="form-control" placeholder="Nama Provinsi" readonly />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-city-id">Origin City ID & Nama</label>
            <div class="col-sm-3">
              <input type="text" name="komerce_shipping_city_id" value="<?php echo $komerce_shipping_city_id; ?>" id="input-city-id" class="form-control" placeholder="ID Kota" readonly />
            </div>
            <div class="col-sm-7">
              <input type="text" name="komerce_shipping_city_name" value="<?php echo $komerce_shipping_city_name; ?>" id="input-city-name" class="form-control" placeholder="Nama Kota" readonly />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-subdistrict-id">Origin Subdistrict ID & Nama</label>
            <div class="col-sm-3">
              <input type="text" name="komerce_shipping_subdistrict_id" value="<?php echo $komerce_shipping_subdistrict_id; ?>" id="input-subdistrict-id" class="form-control" placeholder="ID Kecamatan" readonly />
            </div>
            <div class="col-sm-7">
              <input type="text" name="komerce_shipping_subdistrict_name" value="<?php echo $komerce_shipping_subdistrict_name; ?>" id="input-subdistrict-name" class="form-control" placeholder="Nama Kecamatan" readonly />
              <span class="help-block">ID Daerah otomatis terisi ketika Anda memilih daerah asal melalui <strong>Pilih Origin (Dropdown)</strong> di atas.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">Sinkronisasi Database</label>
            <div class="col-sm-10">
              <button type="button" id="button-sync-db" class="btn btn-warning" style="background-color: #f0ad4e; border-color: #eea236; font-weight: bold;"><i class="fa fa-refresh"></i> SINKRONKAN DATABASE REGIONAL (SYNC DB)</button>
              <span class="help-block" style="margin-top: 8px;">Klik tombol ini untuk mensinkronisasi data regional (Provinsi, Kota, Kecamatan) dari Komerce API langsung ke tabel database OpenCart agar kalkulasi tarif lebih cepat, lengkap, dan ringan.</span>
              <div id="sync-db-loading" style="display:none; margin-top: 10px;" class="alert alert-info">
                <i class="fa fa-spinner fa-spin"></i> Menghubungkan ke API Komerce & mensinkronisasikan tabel <code>oc_komerce_province</code>, <code>oc_komerce_city</code>, dan <code>oc_komerce_subdistrict</code>...
              </div>
              <div id="sync-db-success" style="display:none; margin-top: 10px;" class="alert alert-success">
                <i class="fa fa-check-circle"></i> Sinkronisasi sukses! Database regional dan caching telah siap (0ms latency).
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-weight"><?php echo $entry_default_weight; ?></label>
            <div class="col-sm-10">
              <input type="number" name="komerce_shipping_default_weight" value="<?php echo $komerce_shipping_default_weight; ?>" id="input-weight" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="komerce_shipping_sort_order" value="<?php echo $komerce_shipping_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
    // Load provinces
    function loadProvinces() {
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_provinces_ajax&token=<?php echo $token; ?>',
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Provinsi --</option>';
                var current_province_id = $('#input-province-id').val();
                
                $.each(json, function(index, value) {
                    var selected = (value.province_id == current_province_id) ? ' selected="selected"' : '';
                    html += '<option value="' + value.province_id + '"' + selected + '>' + value.province_name + '</option>';
                });
                
                $('#select-province').html(html);
                
                if (current_province_id) {
                    loadCities(current_province_id);
                }
            }
        });
    }

    // Load cities
    function loadCities(province_id) {
        $('#select-city').html('<option value="">-- Loading Kota... --</option>');
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_cities_ajax&token=<?php echo $token; ?>&province_id=' + province_id,
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Kota --</option>';
                var current_city_id = $('#input-city-id').val();
                
                $.each(json, function(index, value) {
                    var selected = (value.city_id == current_city_id) ? ' selected="selected"' : '';
                    html += '<option value="' + value.city_id + '"' + selected + '>' + value.city_name + '</option>';
                });
                
                $('#select-city').html(html);
                
                if (current_city_id) {
                    loadSubdistricts(current_city_id);
                }
            }
        });
    }

    // Load subdistricts
    function loadSubdistricts(city_id) {
        $('#select-subdistrict').html('<option value="">-- Loading Kecamatan... --</option>');
        $.ajax({
            url: 'index.php?route=extension/shipping/komerce_shipping/get_subdistricts_ajax&token=<?php echo $token; ?>&city_id=' + city_id,
            dataType: 'json',
            success: function(json) {
                var html = '<option value="">-- Pilih Kecamatan --</option>';
                var current_subdistrict_id = $('#input-subdistrict-id').val();
                
                $.each(json, function(index, value) {
                    var selected = (value.subdistrict_id == current_subdistrict_id) ? ' selected="selected"' : '';
                    html += '<option value="' + value.subdistrict_id + '"' + selected + '>' + value.subdistrict_name + '</option>';
                });
                
                $('#select-subdistrict').html(html);
            }
        });
    }

    // Event handlers
    $('#select-province').on('change', function() {
        var province_id = $(this).val();
        var province_name = $(this).find('option:selected').text();
        
        $('#input-province-id').val(province_id);
        $('#input-province-name').val(province_id ? province_name : '');
        
        // Reset city & subdistrict
        $('#input-city-id').val('');
        $('#input-city-name').val('');
        $('#input-subdistrict-id').val('');
        $('#input-subdistrict-name').val('');
        $('#select-city').html('<option value="">-- Pilih Kota --</option>');
        $('#select-subdistrict').html('<option value="">-- Pilih Kecamatan --</option>');
        
        if (province_id) {
            loadCities(province_id);
        }
    });

    $('#select-city').on('change', function() {
        var city_id = $(this).val();
        var city_name = $(this).find('option:selected').text();
        
        $('#input-city-id').val(city_id);
        $('#input-city-name').val(city_id ? city_name : '');
        
        // Reset subdistrict
        $('#input-subdistrict-id').val('');
        $('#input-subdistrict-name').val('');
        $('#select-subdistrict').html('<option value="">-- Pilih Kecamatan --</option>');
        
        if (city_id) {
            loadSubdistricts(city_id);
        }
    });

    $('#select-subdistrict').on('change', function() {
        var subdistrict_id = $(this).val();
        var subdistrict_name = $(this).find('option:selected').text();
        
        $('#input-subdistrict-id').val(subdistrict_id);
        $('#input-subdistrict-name').val(subdistrict_id ? subdistrict_name : '');
    });

    // Initialize dropdowns
    loadProvinces();

    // Re-trigger dropdown reload when DB is synced
    window.reloadAdminRegions = function() {
        loadProvinces();
    };
});

$('#button-sync-db').on('click', function() {
    var button = $(this);
    button.prop('disabled', true);
    $('#sync-db-loading').fadeIn();
    $('#sync-db-success').hide();
    
    $.ajax({
        url: 'index.php?route=extension/shipping/komerce_shipping/sync_db&token=<?php echo $token; ?>',
        dataType: 'json',
        success: function(json) {
            button.prop('disabled', false);
            $('#sync-db-loading').fadeOut(function() {
                if (json['success']) {
                    $('#sync-db-success').removeClass('alert-danger').addClass('alert-success').html('<i class="fa fa-check-circle"></i> ' + json['message']).fadeIn();
                    if (typeof window.reloadAdminRegions === 'function') {
                        window.reloadAdminRegions();
                    }
                } else {
                    $('#sync-db-success').removeClass('alert-success').addClass('alert-danger').html('<i class="fa fa-exclamation-circle"></i> ' + json['message']).fadeIn();
                    alert('Error: ' + json['message']);
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            button.prop('disabled', false);
            $('#sync-db-loading').fadeOut();
            
            var errorMsg = 'Koneksi Gagal: ' + thrownError + ' (HTTP Status: ' + xhr.status + '). ';
            if (xhr.status == 500) {
                errorMsg += 'Server mengembalikan Internal Server Error (500). Ini biasanya berarti akun user database Anda di cPanel/phpMyAdmin kekurangan hak akses (privilese) untuk membuat tabel baru (CREATE TABLE), atau server hosting Anda melarang outbound connection ke Komerce API. Silakan periksa file php_error.log atau error log di hosting Anda.';
            } else {
                errorMsg += 'Silakan hubungi administrator server atau periksa kecocokan token admin Anda.';
            }
            alert(errorMsg);
            
            $('#sync-db-success').removeClass('alert-success').addClass('alert-danger').html('<i class="fa fa-exclamation-circle"></i> ' + errorMsg).fadeIn();
        }
    });
});
//--></script>
<?php echo $footer; ?>