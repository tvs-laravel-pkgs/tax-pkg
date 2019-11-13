app.component('taxCodeList', {
    templateUrl: tax_codes_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#tax_code').DataTable({
            "dom": cndn_dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getTaxCodeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {},
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'tax_codes.code' },
                { data: 'type', name: 'configs.name' },
                { data: 'cgst', name: 'cgst', searchable: false },
                { data: 'sgst', name: 'cgst', searchable: false },
                { data: 'igst', name: 'igst', searchable: false },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html('(' + max + ')')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('#search_tax_code').val(this.value);

        $scope.clear_search = function() {
            $('#search_tax_code').val('');
            $('#tax_code').DataTable().search('').draw();
        }

        var dataTables = $('#tax_code').dataTable();
        $("#search_tax_code").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        $scope.deleteTaxCode = function($id) {
            $('#tax_code_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#tax_code_id').val();
            $http.get(
                tax_codes_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Tax Code Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#tax_code').DataTable().ajax.reload(function(json) {});
                    $location.path('/tax-pkg/tax-code/list');
                }
            });
        }
        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('taxCodeForm', {
    templateUrl: tax_codes_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? tax_codes_codes_get_form_data_url : tax_codes_codes_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            console.log(response);
            self.tax_code = response.data.tax_code;
            self.taxcode_type_list = response.data.taxcode_type_list;
            self.tax_list = response.data.tax_list;
            self.type_list = response.data.type_list;
            self.action = response.data.action;
            if (response.data.action == 'Edit') {
                self.branch_list = response.data.branch_list;
                if (self.tax_code.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
                $.each(self.tax_code.taxes, function(index, value) {
                    $scope.getTaxType(value.id, index);
                });
                $scope.showTypeinTaxes(self.tax_code.type_id);
            } else {
                self.tax_code.taxes = [];
                $scope.add_tax();
                self.switch_value = 'Active';
            }
            $rootScope.loading = false;
        });

        $scope.showTypeinTaxes = function($id) {
            if ($id) {
                $.each(self.taxcode_type_list, function(index, value) {
                    if ($id == value.id) {
                        self.tax_code_type_name = value.name;
                    }
                });
            }
        }

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        //ADD TAX
        $scope.add_tax = function() {
            self.tax_code.taxes.push({});
        }
        //REMOVE TAX 
        $scope.removeTax = function(index) {
            // if (segment_id) {
            //     self.segment_removal_id.push(segment_id);
            //     $('#segment_removal_id').val(JSON.stringify(self.segment_removal_id));
            // }
            self.tax_code.taxes.splice(index, 1);
        }

        //GET TYPE BASED TAX
        $scope.getTaxType = function(id, index) {
            $http.get(
                get_tax_type_based_tax_delete_data_url + '/' + id
            ).then(function(response) {
                $(".type_based_tax_" + index).html(response.data.type);
            });
        }

        jQuery.extend(jQuery.validator.messages, {
            max: jQuery.validator.format("Percentage should be lesser than 100")
        });
        $(document).on('keydown keyup change', '.percentage_check', function(e) {
            var keys_ids = $(this).data("eligible");
            key_id = keys_ids.split("_");
            if ($(this).val() > 100) {
                $('#eligible_amount_data_' + key_id[0] + '_' + key_id[1]).attr({
                    "max": 100
                });
            }
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            invalidHandler: function(event, validator) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveTaxCode'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: res.message,
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $location.path('/tax-pkg/tax-code/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/tax-pkg/tax-code/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});