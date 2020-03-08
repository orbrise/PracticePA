<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//// non auth routes
Route::group(['namespace' => 'Api\v1\AuthController', 'prefix' => 'v1'], function(){
Route::post("register", "AuthController@Register");
Route::post("login", "AuthController@Login");
Route::get('activation/{code?}/', "AuthController@Activation");
Route::post("ForgotPassword", "AuthController@ForgotPassword");
Route::post("NewPassword", "AuthController@NewPassword");
Route::post("CheckStatus", "AuthController@CheckStatus");
Route::post('get-invitation','AuthController@getInvitationData');
});

// only for logout api
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\AuthController', 'prefix' => 'v1'], function(){
Route::post('logout', "AuthController@LogOut");
});

// auth routes
// Company Controller Routes 
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\CompanyController', 'prefix' => 'v1'], function(){
Route::post("company_config", "CompanyController@CompanyConfig");
Route::post('get-company-profile',"CompanyController@getCompanyProfile");
Route::post('update-company-profile',"CompanyController@updateCompanyProfile");
Route::post('getcompany/{key}/{value}/{per_page?}',"CompanyController@GetCompany");
Route::post("company_module", "CompanyController@GetCompanyModule");
Route::post('send-invitation','CompanyController@Invitation');

Route::post('update-invitation-status','CompanyController@updateInvitationStatus');
Route::post('get-active-modules','CompanyController@getCompanyActiveModules');

	});

// Company Controller Routes 
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\ProfileController', 'prefix' => 'v1'], function(){
Route::post('editprofile', "ProfileController@EditProfile");
Route::post('updateprofile', "ProfileController@UpdateProfile");

	});

Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\ClientController', 'prefix' => 'v1'], function(){
Route::post('client-type-add',"ClientController@addClientType");
Route::post('service-type',"ClientController@serviceType");
Route::post('client-add',"ClientController@clientAdd");
Route::post('client-type-list',"ClientController@ClientList");
Route::post('client-address-add',"ClientController@ClientAddressAdd");
Route::post('client-address-delete',"ClientController@ClientAddressDelete");
Route::post('client-address-edit',"ClientController@ClientAddressEdit");
Route::post('client-address-update',"ClientController@ClientAddressUpdate");
Route::post('client-address-show',"ClientController@ClientAddressShow");
Route::post('get-service-type',"ClientController@getServiceType");
Route::post('get-partners',"ClientController@getPartners");
Route::post('get-managers',"ClientController@getManagers");
Route::post('get-payroll-managers',"ClientController@getPayroleManagers");
Route::post('get-all-clients',"ClientController@getAllClients");
Route::post('edit-clients',"ClientController@editClients");
Route::post('single-clients',"ClientController@editClients");
Route::post('update-clients',"ClientController@updateClients");
Route::post('add-trade-code-clients',"ClientController@addTradeCodeClients");
Route::post('get-generate-prefix-code',"ClientController@genratePrefixCode");

// optioal services
Route::post('list-optional-service',"ClientController@ListOptionalServic");
Route::post('service',"ClientController@SaveOptionalService");

Route::post('client-jobs',"ClientController@clientJob");
Route::post('get-generate-prefix-code',"ClientController@generatePrefixCode");
Route::post('getroles', "ClientController@getAllRoles");
Route::post('update-optional-services', "ClientController@UpdateOptionalService");

// client officer
Route::post('add-client-officer',"ClientOfficerController@addClientOfficer");
Route::post('edit-client-officer',"ClientOfficerController@editClientOfficer");
Route::post('update-client-officer',"ClientOfficerController@updateClientOfficer");
Route::post('get-client-all-officer',"ClientOfficerController@getClientOfficer");
Route::post('delete-client-officer',"ClientOfficerController@deleteClientOfficer");
Route::post('active-client-officer',"ClientOfficerController@getClientActiveOfficer");
//testing
Route::post('applykyc',"OnfidoController@createApplicant");

//Billing
Route::post('billing-user-get',"ClientBillingController@GetBillingData")->name('getuserbilling');
Route::post('billing-add',"ClientBillingController@BillingAdd");
Route::post('billing-edit',"ClientBillingController@EditBilling");
Route::post('billing-list',"ClientBillingController@BillingList");
Route::post('billing-update',"ClientBillingController@BillingUpdate");
Route::post('billing-complete',"ClientBillingController@goCardlesComplete");

// KeyService
Route::post('get-company-key-service',"ClienKeyServiceController@getCompanyKeyService");
Route::post('add-client-key-service',"ClienKeyServiceController@addKeyServices");
Route::post('cancel-service-view',"ClienKeyServiceController@cancelServiceView");

Route::post('invoice-list',"ClientInvoiceController@getInvoiceList");
Route::post('invoice-item',"ClientInvoiceController@InvoiceItem");



});
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\CommonController', 'prefix' => 'v1'], function(){
Route::post("getuser", "CommonController@getUser");
Route::post('get-user-roles', "CommonController@getUserRoles");
Route::post("countrylist", "CommonController@countryList");
Route::post("modules", "CommonController@getModules");
Route::post("get_routes", "CommonController@getRoutes");
Route::post("add_permissions", "CommonController@AddNewPermission");
Route::post("get_permission_names", "CommonController@getPermissionNames");
Route::post("get_permissions", "CommonController@getPermissions");

 });

// contact section
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\ContactController', 'prefix' => 'v1'], function(){
	Route::post("contact-add", "ContactController@ContactAdd");
	Route::post("contact-list", "ContactController@ContactList");
	Route::post("contact-delete", "ContactController@ContactDelete");
	Route::post("contact-edit", "ContactController@ContactEdit");
	Route::post("contact-update", "ContactController@ContactUpdate");
 });
// Notes
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\NotesController', 'prefix' => 'v1'], function(){
	Route::post("notes-add", "NotesController@NotesAdd");
	Route::post("notes-list", "NotesController@NotesList");
	Route::post("notes-delete", "NotesController@NotesDelete");
	Route::post("notes-edit", "NotesController@NotesEdit");
	Route::post("notes-update", "NotesController@NotesUpdate");
	Route::post("notes-list-all", "NotesController@NotesListAll");
});
//staff
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\StaffControllers', 'prefix' => 'v1'], function(){
	Route::post("staff-add", "StaffController@StaffAdd");
	Route::post("staff-list", "StaffController@ListStaff");
	Route::post("staff-edit", "StaffController@StaffEdit");
	Route::post("staff-update", "StaffController@StaffUpdate");
	Route::post("update-gocardless-billing","StaffController@updateBilling");
    Route::post("fc","StaffController@goCardlesComplete");
});
//Organization
Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\OrganizationController', 'prefix' => 'v1'], function(){
	Route::post("organization-add", "OrganizationController@OrganizationAdd");
	Route::post("organization-list", "OrganizationController@OrganizationList");
	Route::post("organization-edit", "OrganizationController@OrganizationEdit");
	Route::post("organization-update", "OrganizationController@OrganizationUpdate");
	Route::post("organization-delete", "OrganizationController@OrganizationDelete");
});

Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\TrackManageController', 'prefix' => 'v1'], function(){
	Route::post("get-all-deadlines", "TrackManageController@getAllDeadlines");
	Route::post("get-all-cancelled-deadlines", "TrackManageController@getCanceljobs");
	Route::post("get-all-completed-deadlines", "TrackManageController@getCompletedjobs");
	Route::post("get-all-assigned-deadlines", "TrackManageController@getAssignedjobs");
	Route::post("edit-assigned-jobs-view", "TrackManageController@editAssignedJobsView");
	Route::post("update-assigned-jobs-view", "TrackManageController@updateAssignedJobsView");
	Route::post("update-completed-jobs-view", "TrackManageController@updateCompleteJobsView");
	Route::post("update-cancelled-jobs-view", "TrackManageController@updateCancelJobsView");
});

Route::group(['middleware' => "auth:api", 'namespace' => 'Api\v1\TimeSheetController', 'prefix' => 'v1'], function(){
	Route::post("get-all-time-sheet", "TimeSheetController@getAllTimeSheet");
	Route::post("add-time-sheet", "TimeSheetController@addTimeSheet");
	Route::post("edit-time-sheet", "TimeSheetController@editTimeSheet");
	Route::post("delete-time-sheet", "TimeSheetController@deleteTimeSheet");
	Route::post("update-time-sheet", "TimeSheetController@updateTimeSheet");
	Route::post("client-service-time-sheet", "TimeSheetController@clientServiceTimeSheet");
	Route::post("repost-time-sheet", "TimeSheetController@repostTimeSheet");
	Route::post("post-time-sheet", "TimeSheetController@postTimeSheet");
	Route::post("preview-time-sheet", "TimeSheetController@previewTimeSheet");
	Route::post("add-preview-time-sheet", "TimeSheetController@addPreviewTimeSheet");
	Route::post("time-sheet-invoices-show", "TimeSheetController@timeSheetInvoicesShow");
	Route::post("edit-time-sheet-invoices", "TimeSheetController@editTimesheeInvoice");
	Route::post("update-time-sheet-invoices", "TimeSheetController@updateTimesheeInvoice");
	Route::post("add-time-sheet-invoices-payments", "TimeSheetController@addTimesheeInvoicePayments");

});

Route::group(['namespace' => 'Api\v1\CommonController', 'prefix' => 'v1'], function(){
    Route::post("get_all_routes", "CommonController@getRoutes");
});


