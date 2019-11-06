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
    })

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
    })
    ;