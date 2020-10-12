app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //TAX CODE
    when('/tax-pkg/tax-code/list', {
        template: '<tax-code-list></tax-code-list>',
        title: 'Tax Codes',
    }).
    when('/tax-pkg/tax-code/add', {
        template: '<tax-code-form></tax-code-form>',
        title: 'Add Tax Code',
    }).
    when('/tax-pkg/tax-code/edit/:id', {
        template: '<tax-code-form></tax-code-form>',
        title: 'Edit Tax Code',
    }).

    //TAX
    when('/tax-pkg/tax/list', {
        template: '<tax-list></tax-list>',
        title: 'Taxes',
    }).
    when('/tax-pkg/tax/add', {
        template: '<tax-form></tax-form>',
        title: 'Add Tax',
    }).
    when('/tax-pkg/tax/edit/:id', {
        template: '<tax-form></tax-form>',
        title: 'Edit Tax',
    });
}]);

app.component('taxList', {
    templateUrl: tax_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#taxes').DataTable({
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
            stateSave: true,
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('TDataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function(settings) {
                var state_save_val = JSON.parse(localStorage.getItem('TDataTables_' + settings.sInstance));
                if (state_save_val) {
                    $('#search_taxes').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('TDataTables_' + settings.sInstance));
            },
            paging: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getTaxList'],
                type: "GET",
                dataType: "json",
                data: function(d) {},
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'taxes.name' },
                { data: 'type', name: 'configs.name' },
            ],
            infoCallback: function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_taxes').val('');
            $('#taxes').DataTable().search('').draw();
        }

        var dataTables = $('#taxes').dataTable();
        $("#search_taxes").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        $scope.deleteTax = function($id) {
            $('#tax_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#tax_id').val();
            $http.get(
                tax_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Tax Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#taxes').DataTable().ajax.reload(function(json) {});
                    $location.path('/tax-pkg/tax/list');
                }
            });
        }
        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('taxForm', {
    templateUrl: tax_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? tax_get_form_data_url : tax_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            console.log(response);
            self.tax = response.data.tax;
            self.type_list = response.data.type_list;
            self.action = response.data.action;
            if (response.data.action == 'Edit') {
                if (response.data.tax[0].deleted_at) {
                    self.tax = [];
                    self.tax.push({
                        id: response.data.tax[0].id,
                        name: response.data.tax[0].name,
                        type_id: response.data.tax[0].type_id,
                        switch_value: 'Inactive',
                    });
                } else {
                    self.tax = [];
                    self.tax.push({
                        id: response.data.tax[0].id,
                        name: response.data.tax[0].name,
                        type_id: response.data.tax[0].type_id,
                        switch_value: 'Active',
                    });
                }
            } else {
                $scope.add_tax();
            }
            $rootScope.loading = false;
        });
        //ADD TAX
        $scope.add_tax = function() {
            self.tax.push({
                switch_value: 'Active',
            });
        }
        //REMOVE TAX 
        $scope.removeTax = function(index, tax_id) {
            console.log(index, tax_id);
            if (tax_id) {
                self.tax_removal_id.push(tax_id);
                $('#tax_removal_id').val(JSON.stringify(self.tax_removal_id));
            }
            self.tax.splice(index, 1);
        }

        //VALIDATEOR FOR MULTIPLE 
        $.validator.messages.minlength = 'Minimum of 3 charaters';
        $.validator.messages.maxlength = 'Maximum of 191 charaters';
        jQuery.validator.addClassRules("tax_name", {
            required: true,
            minlength: 3,
            maxlength: 191,
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveTax'],
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
                            $location.path('/tax-pkg/tax/list');
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
                                $location.path('/tax-pkg/tax/list');
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