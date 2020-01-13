@if(config('custom.PKG_DEV'))
    <?php $tax_pkg_prefix = '/packages/abs/tax-pkg/src';?>
@else
    <?php $tax_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var tax_list_template_url = "{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax/list.html')}}";
    var tax_get_form_data_url = "{{url('tax-pkg/taxes/get-form-data/')}}";
    var tax_form_template_url = "{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax/form.html')}}";
    var tax_delete_data_url = "{{url('tax-pkg/taxes/delete/')}}";
</script>
<script type="text/javascript" src="{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax/controller.js?v=2')}}"></script>
 <!-- ------------------------------------------------------------------------------------------ -->
 <!-- ------------------------------------------------------------------------------------------ -->

<script type="text/javascript">
    var tax_codes_list_template_url = "{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax-code/list.html')}}";
    var tax_codes_codes_get_form_data_url = "{{url('tax-pkg/tax-codes/get-form-data/')}}";
    var tax_codes_form_template_url = "{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax-code/form.html')}}";
    var tax_codes_delete_data_url = "{{url('tax-pkg/tax-codes/delete/')}}";
    var get_tax_type_based_tax_delete_data_url = "{{url('tax-pkg/tax-codes/getTaxType/')}}";
    var get_tax_type_list_based_tax_code = "{{route('getTaxListInTaxCode')}}";
</script>
<script type="text/javascript" src="{{URL::asset($tax_pkg_prefix . '/public/angular/tax-pkg/pages/tax-code/controller.js?v=2')}}"></script>
