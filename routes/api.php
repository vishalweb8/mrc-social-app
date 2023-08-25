<?php

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

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:api');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  *');


//**
// AuthController
//**

Route::post('login', 'Api\AuthController@login');
Route::post('socialLogin', 'Api\AuthController@socialLogin');
Route::post('checkUserExists', 'Api\AuthController@checkUserExists');



//**
// Users Controller 
//**

Route::post('register', 'Api\UsersController@register');

// Get All countrie,states and cities for address component
Route::get('getAddressMaster', 'Api\UsersController@getAddressMaster');

// get country code
Route::get('getCountryCode', 'Api\UsersController@getCountryCode');

Route::post('forgotpassword', 'Api\UsersController@forgotpassword');
Route::post('getLanguageLabels', 'Api\UsersController@getLanguageLabels');
Route::post('getAppUpdateStatus', 'Api\UsersController@getAppUpdateStatus');

//***
// Password Controller
//***

//reset password routes
Route::post('user/resetpasswordrequest', 'Api\PasswordController@resetPasswordRequest');
Route::post('user/resetpasswordrequestconfirm', 'Api\PasswordController@resetPasswordRequestConfirm');
Route::post('user/resetpassword', 'Api\PasswordController@resetPassword');

//** 
// Category Controller
//**

Route::get('getTrendingServices', 'Api\CategoryController@getTrendingServices');
Route::get('getTrendingCategories', 'Api\CategoryController@getTrendingCategories');
Route::get('getMainCategory', 'Api\CategoryController@getMainCategory');
Route::post('getMainSubCategories', 'Api\CategoryController@getMainCategory');
Route::post('getSubCategory', 'Api\CategoryController@getSubCategory');
Route::post('getCategoryMetaTags', 'Api\CategoryController@getCategoryMetaTags');

// currently not in use these api
Route::get('getAllServices', 'Api\CategoryController@getAllServices');
Route::post('getNearByBusinesses', 'Api\BusinessController@getNearByBusinesses');
Route::post('getNearByBusinesses1', 'Api\BusinessController@getNearByBusinesses1');

// end not in use

//**
// Business Controller
//**

// Landing Page Routes 


Route::get('getPremiumBusinesses', 'Api\BusinessController@getPremiumBusinesses');
Route::post('getRecentlyAddedBusinessListing', 'Api\BusinessController@getRecentlyAddedBusinessListing');
Route::post('getBusinessListing', 'Api\BusinessController@getBusinessListingByCatId');
Route::post('getBusinessNearByMap', 'Api\BusinessController@getBusinessNearByMap');
Route::get('getPopularBusinesses', 'Api\BusinessController@getPopularBusinesses');


Route::post('getBusinessRatings', 'Api\BusinessController@getBusinessRatings');
Route::get('getBrandingData', 'Api\BusinessController@getBrandingFileOrText');
Route::post('getBusinessDetail', 'Api\BusinessController@getBusinessDetail');
Route::get('getPromotedBusinesses', 'Api\BusinessController@getPromotedBusinesses');

// Autocomplete Route
Route::post('getSearchAutocomplete', 'Api\BusinessController@getSearchAutocomplete');
Route::post('getSearchBusinesses', 'Api\BusinessController@getSearchBusinesses');
// Public Business Profile
Route::get('getPublicBusinessDetail/{url_slug}', 'Api\BusinessController@getPublicBusinessDetail');
Route::post('getPublicBusinessRatings', 'Api\BusinessController@getPublicBusinessRatings');
Route::post('getPublicProductDetails', 'Api\BusinessController@getPublicProductDetails');
Route::post('getPublicServiceDetails', 'Api\BusinessController@getPublicServiceDetails');
Route::post('getPublicSearchBusinesses', 'Api\BusinessController@getSearchBusinesses');

//**
//ProductController
//**
Route::post('getProductDetails', 'Api\ProductController@getProductDetails');

//**
//ServiceController
//**
Route::post('getServiceDetails', 'Api\ServiceController@getServiceDetails');

//**
//AdvertisementController
//**


    Route::post('getAllAdvertisement', 'Api\AdvertisementController@getAllAdvertisement');
    Route::post('getAdsDetailById', 'Api\AdvertisementController@getAdsByIdForView');
       
//**
// OwnerController
 Route::post('getOwnerInfo', 'Api\OwnerController@getOwnerInfo');
 Route::post('getOwners', 'Api\OwnerController@getOwners');

// Middleware for jwt.auth
Route::group(['middleware' => 'jwt.auth'], function () {
//  Business APIs
    Route::post('addBusiness', 'Api\BusinessController@addBusiness');

    Route::post('logout', 'Api\UsersController@logout');
    Route::post('sendAddMemberOTP', 'Api\BusinessController@sendAddMemberOTP');
    Route::post('verifyAgentOTP', 'Api\BusinessController@verifyAgentOTP');
    Route::post('agentSaveUser', 'Api\BusinessController@agentSaveUser');
    Route::post('saveBusiness', 'Api\BusinessController@saveBusiness');
     Route::post('saveBusinessDoc', 'Api\BusinessController@saveBusinessDoc');
     Route::post('saveBusinessDoc1', 'Api\BusinessController@saveBusinessDoc1');
     Route::post('getBusinessDoc', 'Api\BusinessController@getBusinessDoc');
     Route::post('deleteBusinessDoc', 'Api\BusinessController@deleteBusinessDoc');
    Route::post('saveBusinessImages', 'Api\BusinessController@saveBusinessImages');
    Route::post('deleteBusinessImage', 'Api\BusinessController@deleteBusinessImage');
    Route::get('getTimezone', 'Api\BusinessController@getTimezone');
    Route::post('getAgentBusinesses', 'Api\BusinessController@getAgentBusinesses');

//  Business Ratings APIs
    Route::post('addBusinessRating', 'Api\BusinessController@addBusinessRating');
    //Route::post('getBusinessRatings', 'Api\BusinessController@getBusinessRatings');

//  Business  Online stores api  
    Route::post('saveBusinessOnlineStores', 'Api\BusinessController@saveBusinessOnlineStores');
    Route::post('getBusinessOnlineStores', 'Api\BusinessController@getBusinessOnlineStores');
    Route::post('getOnlineStores', 'Api\BusinessController@getOnlineStores');

//  Product APIs
    Route::post('getProductDetails', 'Api\ProductController@getProductDetails');
    Route::post('saveProduct', 'Api\ProductController@saveProduct');
    Route::post('removeProductImage', 'Api\ProductController@removeProductImage');
    Route::post('removeProduct', 'Api\ProductController@removeProduct');

//  Service APIs
    Route::post('getServiceDetails', 'Api\ServiceController@getServiceDetails');
    Route::post('saveService', 'Api\ServiceController@saveService');
    Route::post('removeService', 'Api\ServiceController@removeService');

//  User's APIs
    Route::get('getprofile', 'Api\UsersController@getProfile');
    Route::post('saveprofile', 'Api\UsersController@saveProfile');
    Route::post('changepassword', 'Api\UsersController@changePassword');
    Route::post('saveProfilePicture', 'Api\UsersController@saveProfilePicture');

    //User's Profile Setting APIs

    
    Route::post('addOrUpdateProfileSetting', 'Api\UserSettingController@store');
    Route::post('getSettingByUserId', 'Api\UserSettingController@getuserSettingById');

//  Investment Ideas Routes
    Route::post('getInvestmentIdeas', 'Api\InvestmentController@getInvestmentIdeas');
    Route::post('addInvestmentIdea', 'Api\InvestmentController@addInvestmentIdea');
    Route::post('showInterestOnInvestmentIdea', 'Api\InvestmentController@showInterestOnInvestmentIdea');
    Route::post('getInvestmentIdeaDetails', 'Api\InvestmentController@getInvestmentIdeaDetails');
    Route::post('addInvestmentInterest', 'Api\InvestmentController@saveInvestmentInterest');
    Route::post('getInvestmentInterestDetail', 'Api\InvestmentController@getInvestmentInterestById');
    Route::get('getAllInvestmentInterest', 'Api\InvestmentController@getAllInvestmentInterest');
    Route::post('getMyInvestmentInterest', 'Api\InvestmentController@getMyInvestmentInterest');
    Route::post('deleteInvestmentIdea', 'Api\InvestmentController@deleteInvestmentIdea');
    Route::get('getInvestmentFilters', 'Api\InvestmentController@getInvestmentFilters');

// Agent Request Route
    Route::post('addAgentRequest', 'Api\UsersController@addAgentRequest');

// Business Owner Route
    Route::post('addOwner', 'Api\OwnerController@addOwner');
    Route::post('editOwner', 'Api\OwnerController@editOwner');
    Route::post('deleteOwner', 'Api\OwnerController@deleteOwner');
    Route::post('saveOwnerProfilePicture', 'Api\OwnerController@saveProfilePicture');

// Chats Route
    Route::post('sendMessage', 'Api\ChatsController@sendMessage');
    Route::post('sendEnquiry', 'Api\ChatsController@sendEnquiry');
    Route::post('sendEnquiryToCustomer', 'Api\ChatsController@sendEnquiryToCustomer');
    Route::post('getThreadListing', 'Api\ChatsController@getThreadListing');
    Route::post('getThreadMessages', 'Api\ChatsController@getThreadMessages');
    Route::post('sendEnquiryMessage', 'Api\ChatsController@sendEnquiryMessage');
    Route::get('getUnreadThreadsCount', 'Api\ChatsController@getUnreadThreadsCount');
    Route::post('deleteThread', 'Api\ChatsController@deleteThread');

    // currently not in use
    Route::post('getNewThreadMessages', 'Api\ChatsController@getNewThreadMessages');



// Subscription Route
    Route::get('getSubscriptionPlanList', 'Api\SubscriptionController@getSubscriptionPlanList');
    Route::post('getSubscriptionPlanDetail', 'Api\SubscriptionController@getSubscriptionPlanDetail');



// Business approved
    Route::post('getBusinessApproved', 'Api\BusinessController@getBusinessApproved');

// Get subscription List
   // Route::get('getSubscriptionPlanList', 'Api\SubscriptionController@getSubscriptionPlanList');
    Route::get('getCurrentSubscriptionPlan', 'Api\SubscriptionController@getCurrentSubscriptionPlan');
    
    //  Advertisement APIs
    Route::post('addOrUpdateAds', 'Api\AdvertisementController@addOrUpdateAds');
  
    Route::post('getAdsByIdForEdit', 'Api\AdvertisementController@getAdsByIdForEdit');
    Route::post('deleteAdsImage', 'Api\AdvertisementController@deleteAdsImage');
    Route::post('deleteAdsVideoLink', 'Api\AdvertisementController@deleteAdsVideoLink');
    Route::post('getMyAdvertisement', 'Api\AdvertisementController@getMyAdvertisement');
   
    Route::post('removeAdvertisement', 'Api\AdvertisementController@removeAdvertisement');

    //  Advertisement send Inquiry and show Interest.
    Route::post('sendAdvertisementEnquiry', 'Api\ChatsController@sendAdvertisementEnquiry');
    Route::post('removeInterestedAdvertisement', 'Api\AdvertisementController@removeInterestedAdvertisement');

    Route::post('getAllInterestedAdvertisement', 'Api\AdvertisementController@getAllInterestedAdvertisement');

    Route::post('uploadAdsImage', 'Api\AdvertisementController@uploadAdsImage');

    Route::post('getLifetimeMembers', 'Api\BusinessController@getLifetimeMembers');
    Route::post('getInterestResponses', 'Api\AdvertisementController@getInterestResponses');

    // PayTM APIs
    //Route::post('purchasePlan','Api\OrderController@order');
    Route::post('payg_payment','Api\OrderController@payg_payment');
    Route::post('payg_payment_android','Api\OrderController@payg_payment_android');
    Route::post('payg_payment_android_reponse','Api\OrderController@payg_payment_android_reponse');
    //Route::post('premiumTransactionPaytm','Api\OrderController@AndroidOrder');
    Route::post('emailUpdate', 'Api\UsersController@emailUpdate');
    Route::post('getSettings', 'Controller@getSettings');

    // for entity claim routes
    Route::post('claimEntity', 'Admin\EntityClaimController@store');
    // Group (Sites)
    Route::post('createOrUpdateSite', 'Api\SiteController@store');
    Route::post('getSite', 'Api\SiteController@show');
    Route::post('joinOrLeaveSite', 'Api\SiteController@joinOrLeaveSite');
    Route::post('acceptInvitation', 'Api\SiteController@acceptInvitation');
    Route::post('siteMembers', 'Api\SiteController@getMembers');
    Route::post('pendingJoinMembers', 'Api\SiteController@pendingJoinMembers');
    Route::post('suggestSiteMembers', 'Api\SiteController@suggestSiteMembers');
    Route::post('siteRoles', 'Api\SiteController@siteRoles');
    Route::post('assignRoleSiteMember', 'Api\SiteController@assignRoleSiteMember');
});
Route::post('sites', 'Api\SiteController@index');
Route::post('siteAutoSuggestion', 'Api\SiteController@siteAutoSuggestion');

// Add membership request

Route::post('sendMembershipRequest', 'Api\BusinessController@sendMembershipRequest');
Route::get('getMembershipPageDetails', 'Api\BusinessController@getMembershipPageDetails');
Route::post('doPaymentOrderDone', 'Api\BusinessController@doPaymentOrderDone');

// Get CMS
Route::get('getCMSList', 'Api\UsersController@getCms');

// send OTP for user registeration
Route::post('sendRegisterOTP', 'Api\UsersController@sendRegisterOTP');

// update notification
Route::post('updateNotificationToken', 'Api\UsersController@updateNotificationToken');

//  notification list
Route::post('notificationList', 'Api\UsersController@notificationList');
Route::post('deleteNotification', 'Api\UsersController@deleteNotification');

Route::get('representative', 'Api\UsersController@representative');

Route::post('stateList','Api\BusinessController@stateList')->name('stateListByCountryName');
Route::post('cityList','Api\BusinessController@cityList')->name('cityListByStateName');
Route::post('districtList','Api\BusinessController@districtList')->name('districtListByStatename');


// Get Subscription without login
Route::get('getSubscriptionPlanListNoAuth', 'Api\SubscriptionController@getSubscriptionPlanListNoAuth');

//PayG APIs
Route::post('payment/status','Api\OrderController@paygCallback');
//Route::post('premiumTransactionPaytmVerify','Api\OrderController@AndroidOrderVerfiy');


//PayTM APIs
//Route::post('payment/status','Api\OrderPayGController@paymentCallback');
//Route::post('premiumTransactionPaytmVerify','Api\OrderPayGController@AndroidOrderVerfiy');



// Public Business Profile
//Route::get('getPublicBusinessDetail/{url_slug}', 'Api\BusinessController@getPublicBusinessDetail');
//Route::post('getPublicBusinessRatings', 'Api\BusinessController@getPublicBusinessRatings');
//Route::post('getPublicProductDetails', 'Api\BusinessController@getPublicProductDetails');
//Route::post('getPublicServiceDetails', 'Api\BusinessController@getPublicServiceDetails');

Route::get('getPublicWebsiteTemplate/{slug}', 'Api\PublicWebsiteController@getDetail');
Route::get('getAllAdvisors', 'Api\PublicWebsiteController@getAllAdvisors');

Route::post('saveBusinessCategories', 'Api\BusinessCategoryController@store');
// Public Inquiry API

Route::post('storePublicInquiry', 'Api\PublicInquiryController@store');

Route::post('storePublicReview', 'Api\PublicReviewController@store');

Route::get('getBusinessBrandings', 'Api\BusinessBrandingController@getBusinessBrandings');
Route::post('addBusinesBrandingInquiry', 'Api\BusinessBrandingController@addBusinesBrandingInquiry');
// public post
Route::get('getCategoryAndModKeywords', 'Api\PublicPostController@getCategoryAndModKeywords');
Route::post('getAllPosts', 'Api\PublicPostController@index');
Route::post('getPostDetail', 'Api\PublicPostController@show')->middleware('jwt.auth');
Route::post('storePost', 'Api\PublicPostController@store')->middleware('jwt.auth');
Route::post('updatePost', 'Api\PublicPostController@update')->middleware('jwt.auth');
Route::post('deletePost', 'Api\PublicPostController@destroy')->middleware('jwt.auth');
Route::post('likePost', 'Api\PublicPostController@like')->middleware('jwt.auth');
Route::post('getLikedUsers', 'Api\PublicPostController@getLikedUsers')->middleware('jwt.auth');
Route::post('commentPost', 'Api\PublicPostController@comment')->middleware('jwt.auth');

// Contactus Route
Route::post('contactUs', 'Api\UsersController@contactUs');
Route::post('sendNotifications', 'Api\UsersController@sendNotifications')->name('send.notifications');
Route::post('getNotification', 'Api\UsersController@getNotification')->name('get.notification');
Route::post('getNotificationFilters', 'Api\UsersController@getNotificationFilters');
Route::post('getLocationByPincode', 'Api\UsersController@getLocationByPincode')->name('getLocationByPincode');
Route::post('getAllNotifications', 'Api\UsersController@getGroupNotifications')->middleware('jwt.auth')->name('get.all.notifications');

// Get CMS
Route::post('getLocationById', 'Api\UsersController@getLocation');


// for entity routes
Route::post('getSubAssetTypeFields', 'Admin\AssetTypeController@getSubAssetFields')->name('getSubAssetTypeFields');
Route::post('getEntityInOtherLang', 'Api\BusinessController@getEntityInOtherLang')->name('getEntityInOtherLang');
Route::post('getEntityKnowMore', 'Api\BusinessController@getEntityKnowMore')->name('getEntityKnowMore');
Route::post('getEntityCustomDetails', 'Api\BusinessController@getEntityCustomDetails')->name('getEntityCustomDetails');
Route::post('getEntityNearBy', 'Api\BusinessController@getEntityNearBy')->name('getEntityNearBy');
//Route::post('getFilterEntitiesForNearBy', 'Api\BusinessController@getFilterEntitiesForNearBy');
Route::post('suggestDescription', 'Api\BusinessController@suggestDescription')->middleware('jwt.auth');
Route::post('reportEntity', 'Api\BusinessController@reportEntity')->middleware('jwt.auth');
Route::post('reportPost', 'Api\BusinessController@reportPost')->middleware('jwt.auth');
Route::post('getReasonsByEntityType', 'Admin\ReasonController@getReasonsByEntityType');
Route::post('getSponsoredEntity', 'Api\BusinessController@getSponsoredEntity');
Route::post('getVideoById', 'Api\BusinessController@getVideoById');
Route::post('getEntityVideo', 'Api\BusinessController@getEntityVideo');

// Plasma donor route
Route::post('getAllPlasmaDonor', 'Api\PlasmaDonorController@index');
Route::post('storePlasmaDonor', 'Api\PlasmaDonorController@store');
Route::post('updatePlasmaDonor', 'Api\PlasmaDonorController@update');
Route::post('deletePlasmaDonor', 'Api\PlasmaDonorController@destroy');

Route::post('/getSubCategoryByCategoryId', 'Api\CategoryController@getSubCategoryByCategoryId')->name('getSubCategory');
Route::post('/getSubAssetByAsset', 'Admin\AssetTypeController@getSubAssetByAsset')->name('getSubAssetByAsset');

Route::post('generateQrCode','Api\BusinessController@qrCodeGenerate');
Route::post('homepage', 'Api\ApplicationsController@homepage');


// Autocomplete New Route
Route::post('searchAutocomplete', 'Api\BusinessController@searchAutocomplete');
Route::post('searchBusinesses', 'Api\BusinessController@searchBusinesses');

//  Job Vacency 
Route::post('createUpdateJob', 'Api\JobVacancyController@store')->middleware('jwt.auth');
Route::post('myJobs', 'Api\JobVacancyController@myJobs')->middleware('jwt.auth');
Route::post('deleteJob', 'Api\JobVacancyController@deleteJob')->middleware('jwt.auth');
Route::post('jobList', 'Api\JobVacancyController@jobList')->middleware('jwt.auth');
Route::post('applyJob', 'Api\JobVacancyController@applyJob')->middleware('jwt.auth');
Route::post('jobApplyList', 'Api\JobVacancyController@jobApplyList')->middleware('jwt.auth');
Route::post('JobDetails', 'Api\JobVacancyController@JobDetails')->middleware('jwt.auth');




