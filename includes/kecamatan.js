jQuery.noConflict();
(function ($) {
    $('#billing_kecamatan').keyup(get_search_results);

    function get_search_results() {
        var search_query = jQuery('#billing_kecamatan').val();
        if (search_query != "" && search_query.length > 3) {
            $('#kecamatan-wrapper').empty();
            var url_send_to_api = "http://127.0.0.1:8080/wptemplates/api-ongkir/api.php/denpasar?filter=kecamatan,cs," + search_query;
            $.ajax({
                url: url_send_to_api,
                type: 'GET',
                statusCode: {
                    404: function () {
                        alert("page not found");
                    }
                },
                complete: function (data) {
                    write_results(data);
                    console.log('ajax front end')
                }

            });

            //write result to page
            function write_results(data) {
                if ($('#' + 'kecamatan-wrapper').length == 0) {
                    $('.update-kecamatan').append('<div id="kecamatan-wrapper"> </div>');
                    var p = $('#billing_kecamatan').position();
                    $('#kecamatan-wrapper').css({left: p.left + 1, top: p.top + 45});
                }

                jsonObject = php_crud_api_transform(JSON.parse(data.responseText));
                var tujuan = jsonObject[Object.keys(jsonObject)[0]];
                for (var i = 0; i < tujuan.length; i++) {
                    $('#kecamatan-wrapper').append('<div class="kecamatan-select">' + tujuan[i]['kecamatan'] + ', ' + tujuan[i]['kota'] + '</div>');
                }
            }
        }
        else {
            console.log('input kecamatan belum lengkap atau salah');
        }
    }

    /* get value kecamatan
     *
     *  input from on click .select_kecamatan
     * return kecamatan, kota
     * */

    $(document).on('click', '.kecamatan-select', function () {
        var val = $(this).text().split(',');
        var kecamatan = val[0];
        var kota = $.trim(val[1]);
        $('#billing_kecamatan').val(kecamatan);
        $('#billing_city').val(kota);
        $('#kecamatan-wrapper').remove();
        $.ajax({
            url: jnekecamatan.ajax_url,//http://localhost/woocommerce/wp-admin/admin-ajax.php?action=select_kecamatan_JNE&kecamatan=kartoharjo&kota=kota+denpasar
            type: 'post',
            data: {
                action: 'select_kecamatan_JNE',
                kota: kota,
                kecamatan: kecamatan
            },
            beforeSend: function () {
                console.log('start request xhr' + kota + kecamatan);
            },
            error: function () {
                console.log('errrorr');
            },
            success: function (response) {
                console.log('sukses' + response);
            },
        });

        return false;
    });

    function php_crud_api_transform(tables) {
        var array_flip = function (trans) {
            var key, tmp_ar = {};
            for (key in trans) {
                tmp_ar[trans[key]] = key;
            }
            return tmp_ar;
        };
        var get_objects = function (tables, table_name, where_index, match_value) {
            var objects = [];
            for (var record in tables[table_name]['records']) {
                record = tables[table_name]['records'][record];
                if (!where_index || record[where_index] == match_value) {
                    var object = {};
                    for (var index in tables[table_name]['columns']) {
                        var column = tables[table_name]['columns'][index];
                        object[column] = record[index];
                        for (var relation in tables) {
                            var reltable = tables[relation];
                            for (var key in reltable['relations']) {
                                var target = reltable['relations'][key];
                                if (target == table_name + '.' + column) {
                                    column_indices = array_flip(reltable['columns']);
                                    object[relation] = get_objects(tables, relation, column_indices[key], record[index]);
                                }
                            }
                        }
                    }
                    objects.push(object);
                }
            }
            return objects;
        };
        tree = {};
        for (var name in tables) {
            var table = tables[name];
            if (!table['relations']) {
                tree[name] = get_objects(tables, name);
                if (table['results']) {
                    tree['_results'] = table['results'];
                }
            }
        }
        return tree;
    }

})(jQuery);