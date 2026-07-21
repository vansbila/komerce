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
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        
        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-general" data-toggle="tab"><i class="fa fa-cog"></i> General Settings</a></li>
          <li><a href="#tab-sync" data-toggle="tab"><i class="fa fa-refresh"></i> Komerce DB Sync & Cache</a></li>
        </ul>
        
        <div class="tab-content">
          <!-- GENERAL SETTINGS TAB -->
          <div class="tab-pane active" id="tab-general">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-shipping" class="form-horizontal">
              
              <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-apikey"><?php echo $entry_apikey; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="rajaongkir_apikey" value="<?php echo $rajaongkir_apikey; ?>" placeholder="RajaOngkir API Key" id="input-apikey" class="form-control" />
                  <?php if ($error_apikey) { ?>
                  <div class="text-danger"><?php echo $error_apikey; ?></div>
                  <?php } ?>
                  <span class="help-block">Dapatkan API Key di <a href="https://rajaongkir.com" target="_blank">rajaongkir.com</a></span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-api-type"><?php echo $entry_api_type; ?></label>
                <div class="col-sm-10">
                  <select name="rajaongkir_api_type" id="input-api-type" class="form-control">
                    <option value="starter" <?php if ($rajaongkir_api_type == 'starter') echo 'selected="selected"'; ?>>Starter (Gratis - JNE, POS, TIKI)</option>
                    <option value="basic" <?php if ($rajaongkir_api_type == 'basic') echo 'selected="selected"'; ?>>Basic (Berbayar - Lebih Banyak Kurir)</option>
                    <option value="pro" <?php if ($rajaongkir_api_type == 'pro') echo 'selected="selected"'; ?>>Pro (Berbayar - Mendukung Kecamatan/Subdistrict)</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-origin-province">Provinsi Asal Pengiriman</label>
                <div class="col-sm-10">
                  <input type="text" name="rajaongkir_origin_province" value="<?php echo $rajaongkir_origin_province; ?>" placeholder="ID Provinsi Asal (e.g. 9)" id="input-origin-province" class="form-control" />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-origin-city">Kota / Kabupaten Asal</label>
                <div class="col-sm-10">
                  <input type="text" name="rajaongkir_origin_city" value="<?php echo $rajaongkir_origin_city; ?>" placeholder="ID Kota Asal (e.g. 23 untuk Bandung)" id="input-origin-city" class="form-control" />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-origin-subdistrict">Kecamatan Asal (Khusus Akun Pro)</label>
                <div class="col-sm-10">
                  <input type="text" name="rajaongkir_origin_subdistrict" value="<?php echo $rajaongkir_origin_subdistrict; ?>" placeholder="ID Kecamatan Asal (e.g. 301)" id="input-origin-subdistrict" class="form-control" />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_courier; ?></label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php $avail_couriers = array('jne' => 'JNE', 'pos' => 'POS Indonesia', 'tiki' => 'TIKI', 'jnt' => 'J&T', 'sicepat' => 'SiCepat', 'wahana' => 'Wahana'); ?>
                    <?php foreach ($avail_couriers as $code => $name) { ?>
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="rajaongkir_couriers[]" value="<?php echo $code; ?>" <?php if (in_array($code, $rajaongkir_couriers)) echo 'checked="checked"'; ?> />
                        <?php echo $name; ?>
                      </label>
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-weight-class"><?php echo $entry_weight_class; ?></label>
                <div class="col-sm-10">
                  <select name="rajaongkir_weight_class_id" id="input-weight-class" class="form-control">
                    <?php foreach ($weight_classes as $weight_class) { ?>
                    <option value="<?php echo $weight_class['weight_class_id']; ?>" <?php if ($weight_class['weight_class_id'] == $rajaongkir_weight_class_id) echo 'selected="selected"'; ?>><?php echo $weight_class['title']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="rajaongkir_status" id="input-status" class="form-control">
                    <option value="1" <?php if ($rajaongkir_status) echo 'selected="selected"'; ?>><?php echo $text_enabled; ?></option>
                    <option value="0" <?php if (!$rajaongkir_status) echo 'selected="selected"'; ?>><?php echo $text_disabled; ?></option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="rajaongkir_sort_order" value="<?php echo $rajaongkir_sort_order; ?>" id="input-sort-order" class="form-control" />
                </div>
              </div>

            </form>
          </div>
          
          <!-- KOMERCE DB SYNC & CACHE TAB -->
          <div class="tab-pane" id="tab-sync">
            <div class="row">
              <div class="col-sm-12">
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i> <strong>Optimasi Database Komerce:</strong> Sinkronisasikan database regional Indonesia langsung ke server OpenCart Anda agar pencarian data provinsi, kota, dan kecamatan saat checkout berjalan instan tanpa bergantung terus-menerus ke API eksternal. Kami juga menyediakan cache tarif untuk menghemat batasan kuota API RajaOngkir Anda.
                </div>
              </div>
            </div>
            
            <div class="row" style="margin-bottom: 20px;">
              <!-- Statistics Cards -->
              <div class="col-sm-3">
                <div class="well text-center" style="background:#fff; border:1px solid #e2e8f0; margin-bottom:0;">
                  <h4 style="color:#64748b; font-size:11px; font-weight:bold; text-transform:uppercase; margin-top:0;">Provinsi Lokal</h4>
                  <h2 id="prov-count" style="margin:5px 0; font-weight:800; color:#2563eb;">0</h2>
                  <p style="font-size:10px; color:#94a3b8; margin-bottom:0;">Tabel: komerce_province</p>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="well text-center" style="background:#fff; border:1px solid #e2e8f0; margin-bottom:0;">
                  <h4 style="color:#64748b; font-size:11px; font-weight:bold; text-transform:uppercase; margin-top:0;">Kota/Kab Lokal</h4>
                  <h2 id="city-count" style="margin:5px 0; font-weight:800; color:#2563eb;">0</h2>
                  <p style="font-size:10px; color:#94a3b8; margin-bottom:0;">Tabel: komerce_city</p>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="well text-center" style="background:#fff; border:1px solid #e2e8f0; margin-bottom:0;">
                  <h4 style="color:#64748b; font-size:11px; font-weight:bold; text-transform:uppercase; margin-top:0;">Kecamatan Lokal</h4>
                  <h2 id="subdistrict-count" style="margin:5px 0; font-weight:800; color:#2563eb;">0</h2>
                  <p style="font-size:10px; color:#94a3b8; margin-bottom:0;">Tabel: komerce_subdistrict</p>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="well text-center" style="background:#fff; border:1px solid #e2e8f0; margin-bottom:0;">
                  <h4 style="color:#64748b; font-size:11px; font-weight:bold; text-transform:uppercase; margin-top:0;">Cache Tarif Aktif</h4>
                  <h2 id="cache-count" style="margin:5px 0; font-weight:800; color:#10b981;">0</h2>
                  <p style="font-size:10px; color:#94a3b8; margin-bottom:0;">Tabel: komerce_shipping_cache</p>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-4">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-gears"></i> Kendali Sinkronisasi</h3>
                  </div>
                  <div class="panel-body">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                      <button type="button" id="button-sync-prov" class="btn btn-primary btn-block" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Syncing..."><i class="fa fa-globe"></i> Sync Data Provinsi</button>
                      <button type="button" id="button-sync-city" class="btn btn-primary btn-block" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Syncing..."><i class="fa fa-building"></i> Sync Data Kota/Kabupaten</button>
                      <button type="button" id="button-sync-sub" class="btn btn-info btn-block" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Syncing..."><i class="fa fa-map-marker"></i> Sync Data Kecamatan (Akun Pro)</button>
                      <hr style="margin: 10px 0; border-top:1px solid #e2e8f0;" />
                      <button type="button" id="button-clear-cache" class="btn btn-danger btn-block" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Clearing..."><i class="fa fa-trash-o"></i> Bersihkan Cache Ongkir</button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-sm-8">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-terminal"></i> Console Log & Sync Progress</h3>
                  </div>
                  <div class="panel-body" style="background: #0f172a; color: #38bdf8; font-family: monospace; height: 210px; overflow-y: auto; border-radius: 6px; padding: 15px; font-size: 11px; border: 1px solid #1e293b;">
                    <div id="progress-bar-container" style="display:none; margin-bottom:12px; background:#1e293b; border-radius:4px; overflow:hidden; padding:2px;">
                      <div class="progress" style="margin-bottom:0; height:18px; background:none; box-shadow:none;">
                        <div id="sync-progress" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 0%; font-weight:bold; line-height:18px; font-size:11px;">0%</div>
                      </div>
                      <div id="sync-log-sub" style="color: #a7f3d0; margin-top:5px; font-size:10px;">Menghubungkan ke API...</div>
                    </div>
                    <div id="sync-log">
                      <div style="color: #64748b;">[System Ready] Klik salah satu tombol sinkronisasi di sebelah kiri untuk memulai.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function updateStats() {
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&action=stats',
        dataType: 'json',
        success: function(json) {
            if (json.stats) {
                $('#prov-count').text(json.stats.province);
                $('#city-count').text(json.stats.city);
                $('#subdistrict-count').text(json.stats.subdistrict);
                $('#cache-count').text(json.stats.cache);
            }
        }
    });
}

$(document).ready(function() {
    if ('<?php echo $token; ?>' !== '') {
        updateStats();
    }
});

$('#button-clear-cache').on('click', function() {
    if (!confirm('Apakah Anda yakin ingin menghapus semua cache ongkos kirim?')) return;
    var btn = $(this);
    btn.button('loading');
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&action=clear_cache',
        dataType: 'json',
        success: function(json) {
            btn.button('reset');
            if (json.success) {
                $('#sync-log').append('<div style="color: #10b981;">[Success] ' + json.success + '</div>');
                updateStats();
            } else if (json.error) {
                $('#sync-log').append('<div style="color: #ef4444;">[Error] ' + json.error + '</div>');
            }
        }
    });
});

$('#button-sync-prov').on('click', function() {
    var btn = $(this);
    btn.button('loading');
    $('#sync-log').append('<div style="color: #38bdf8;">[Province] Memulai sinkronisasi daftar Provinsi...</div>');
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&action=province',
        dataType: 'json',
        success: function(json) {
            btn.button('reset');
            if (json.success) {
                $('#sync-log').append('<div style="color: #10b981;">[Success] ' + json.success + '</div>');
                updateStats();
            } else if (json.error) {
                $('#sync-log').append('<div style="color: #ef4444;">[Error] ' + json.error + '</div>');
            }
        },
        error: function() {
            btn.button('reset');
            $('#sync-log').append('<div style="color: #ef4444;">[Connection Error] Gagal menghubungi endpoint server.</div>');
        }
    });
});

$('#button-sync-city').on('click', function() {
    var btn = $(this);
    btn.button('loading');
    $('#sync-log').append('<div style="color: #38bdf8;">[City] Memulai sinkronisasi Kota & Kabupaten...</div>');
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&action=city',
        dataType: 'json',
        success: function(json) {
            btn.button('reset');
            if (json.success) {
                $('#sync-log').append('<div style="color: #10b981;">[Success] ' + json.success + '</div>');
                updateStats();
            } else if (json.error) {
                $('#sync-log').append('<div style="color: #ef4444;">[Error] ' + json.error + '</div>');
            }
        },
        error: function() {
            btn.button('reset');
            $('#sync-log').append('<div style="color: #ef4444;">[Connection Error] Gagal menghubungi endpoint server.</div>');
        }
    });
});

// Incremental Subdistrict Sync for Akun Pro
var sync_cities = [];
var current_sync_index = 0;
var total_cities = 0;

$('#button-sync-sub').on('click', function() {
    var btn = $(this);
    btn.button('loading');
    $('#sync-log').append('<div style="color: #e2e8f0;">[Subdistrict] Mengambil daftar kota local...</div>');
    
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&action=subdistrict',
        dataType: 'json',
        success: function(json) {
            if (json.cities && json.cities.length > 0) {
                sync_cities = json.cities;
                total_cities = sync_cities.length;
                current_sync_index = 0;
                $('#sync-log').append('<div style="color: #38bdf8;">[Subdistrict] Berhasil memuat ' + total_cities + ' kota lokal. Memulai download kecamatan...</div>');
                $('#progress-bar-container').show();
                syncNextCitySubdistrict(btn);
            } else if (json.error) {
                btn.button('reset');
                $('#sync-log').append('<div style="color: #ef4444;">[Error] ' + json.error + '</div>');
            } else {
                btn.button('reset');
                $('#sync-log').append('<div style="color: #f59e0b;">[Warning] Belum ada data kota lokal. Harap lakukan "Sync Data Kota/Kabupaten" terlebih dahulu!</div>');
            }
        },
        error: function() {
            btn.button('reset');
            $('#sync-log').append('<div style="color: #ef4444;">[Connection Error] Gagal memuat daftar kota.</div>');
        }
    });
});

function syncNextCitySubdistrict(btn) {
    if (current_sync_index >= total_cities) {
        btn.button('reset');
        $('#progress-bar-container').hide();
        $('#sync-log').append('<div style="color: #10b981; font-weight:bold;">[Subdistrict] Selesai! Semua data kecamatan berhasil disinkronisasikan ke komerce_subdistrict.</div>');
        updateStats();
        return;
    }
    
    var city = sync_cities[current_sync_index];
    var pct = Math.round((current_sync_index / total_cities) * 100);
    $('#sync-progress').css('width', pct + '%').text(pct + '%');
    
    $.ajax({
        url: 'index.php?route=extension/shipping/rajaongkir/sync&token=<?php echo $token; ?>&city_id=' + city.city_id + '&action=subdistrict',
        dataType: 'json',
        success: function(json) {
            if (json.success) {
                $('#sync-log-sub').html('Progress: Mengunduh Kecamatan ' + city.city_name + ' (' + (current_sync_index+1) + '/' + total_cities + ')');
            } else if (json.error) {
                $('#sync-log').append('<div style="color: #ef4444;">[Skip] ' + city.city_name + ': ' + json.error + '</div>');
            }
            current_sync_index++;
            syncNextCitySubdistrict(btn);
        },
        error: function() {
            $('#sync-log').append('<div style="color: #ef4444;">[Skip Error] Gagal sinkronisasi ' + city.city_name + ', dilewatkan.</div>');
            current_sync_index++;
            syncNextCitySubdistrict(btn);
        }
    });
}
//--></script>
<?php echo $footer; ?>