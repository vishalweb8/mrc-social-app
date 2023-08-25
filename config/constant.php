<?php

return [
    
    'DEFAULT_RADIUS' => '25',
    'RECENTLY_ADDED_BUSINESS_COUNT' => '200',
    'ADMIN_RECORD_PER_PAGE' => '10',
    'WEBSITE_RECORD_PER_PAGE' => '10',
    'WEBSITE_RECENTLY_ADDED_BUSINESS_RECORD_PER_PAGE' => '10',
    'WEBSITE_PLATFORM' => 'web',
    'MOBILE_PLATFORM' => 'mobile',
    'API_RECORD_PER_PAGE' => '20',
    'MOBILE_CHAT_RECORD_PER_PAGE' => '50',
    'WEB_CHAT_RECORD_PER_PAGE' => '30',
    'ACTIVE_FLAG' => '1',
    'INACTIVE_FLAG' => '2',
    'DELETED_FLAG' => '3',
    'USER_ROLE_ID' => '2',
    'SUPER_ADMIN_ROLE_ID' => '1',
    'CATEGORY_TEMP_PATH' => 'images/default2.png',
    'VERIFIED_IMAGE' => 'images/varified.png',
    'NON_VERIFIED_IMAGE' => 'images/noneverified.png',
    'NON_VERIFIED_IMAGE2' => 'images/noneverified-2.png',
    'DEFAULT_IMAGE' => 'images/default2.png',
    'PUBLIC_WEBSITE_ORIGINAL_IMAGE' => 'publicwebsite/original/',
    'PUBLIC_WEBSITE_THUMBNAIL_IMAGE' => 'publicwebsite/thumbnail/',
    'PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_HEIGHT' => '500',
    'PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_WIDTH' => '500',
    'RYEC_DEFAULT_BANNER_IMAGE' => 'images/ryecDefault.png',
    'BUSINESS_ORIGINAL_IMAGE_PATH' => 'business/original/',
    'BUSINESS_THUMBNAIL_IMAGE_PATH' => 'business/thumbnail/',
    'SERVICE_ORIGINAL_IMAGE_PATH' => 'service/original/',
    'SERVICE_THUMBNAIL_IMAGE_PATH' => 'service/thumbnail/',
    'SERVICE_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'SERVICE_THUMBNAIL_IMAGE_WIDTH' => '60',
    'PRODUCT_ORIGINAL_IMAGE_PATH' => 'product/original/',
    'PRODUCT_THUMBNAIL_IMAGE_PATH' => 'product/thumbnail/',
    'PRODUCT_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'PRODUCT_THUMBNAIL_IMAGE_WIDTH' => '60',
    'USER_PROFILE_PIC_WIDTH' => '100',
    'USER_PROFILE_PIC_HEIGHT' => '100',
    'USER_ORIGINAL_IMAGE_PATH' => 'user/original/',
    'USER_THUMBNAIL_IMAGE_PATH' => 'user/thumbnail/',
    'USER_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'USER_THUMBNAIL_IMAGE_WIDTH' => '60',
    'BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT' => '500',
    'BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH' => '500',
    'BUSINESS_THUMBNAIL_IMAGE_HEIGHT' => '300',
    'BUSINESS_THUMBNAIL_IMAGE_WIDTH' => '300',
    'AD_GROUP_BY_DISTANCE_MOD' => '1000',
    's3url' => env('S3_URL','https://ryec1-inx1.s3.amazonaws.com/'),
    'DISK' => env('CLOUD_STORAGE','public'),
    'APP_SHORT_NAME' => env('APP_SHORT_NAME','MRC'),
    'FRONT_END_URL' => env('FRONT_END_URL','https://www.myrajasthanclub.com/'),
    
    'BUSINESS_BRANDING_IMAGE_PATH' => 'business_branding/',
    'POST_IMAGE_PATH' => 'post/',
    'POST_VIDEO_PATH' => 'post/video/',
    'SITE_IMAGE_PATH' => 'site/',
    'ENTITY_CLAIM_DOCUMENT_PATH' => 'entity_claim/',
    'ONLINE_STORE_IMAGE_PATH' => 'online_store/',
    'VIDEO_PATH' => 'video_sharing/',

//   CATEGORY CONSTANT
    'CATEGORY_LOGO_ORIGINAL_IMAGE_PATH' => 'category/category_logo/original/',
    'CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH' => 'category/category_logo/thumbnail/',
    'CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH' => '60',

    'CATEGORY_BANNER_ORIGINAL_IMAGE_PATH' => 'category/category_banner_image/',
    'ADVISOR_IMAGE_PATH' => 'advisor/',

//  Investment Ideas
    'INVESTMENT_IDEAS_FILE_ORIGINAL_IMAGES_PATH' => 'investment_opportunities/investment_images/',
    'INVESTMENT_IDEAS_FILE_ORIGINAL_VIDEOS_PATH' => 'investment_opportunities/investment_videos/',
    'INVESTMENT_IDEAS_FILE_ORIGINAL_DOCS_PATH' => 'investment_opportunities/investment_docs/',

    'ANDROID_APP_VERSION' => env('ANDROID_APP_VERSION', 1),
    'ANDROID_APP_FORCE_UPDATE' => env('ANDROID_APP_FORCE_UPDATE', TRUE),
    'IOS_APP_VERSION' => env('IOS_APP_VERSION', 1),
    'IOS_APP_FORCE_UPDATE' => env('IOS_APP_FORCE_UPDATE', TRUE),
    'LANGUAGE_LABELS_VERSION' => env('LANGUAGE_LABELS_VERSION', '8'),
    // for show/hide payment option in IOS app
    'IS_IPHONE' => env('IS_IPHONE', true),

    'ADMIN_EMAIL' => env('ADMIN_EMAIL'),
    'AGENT_APPROVED_FLAG' => '1',
    'AGENT_DECLINE_FLAG' => '2',

    'BUSINESS_WORKING_OPEN_FLAG' => '1',
    'BUSINESS_WORKING_CLOSE_FLAG' => '0',
    'BUSINESS_DETAILS_RATINGS_LIMIT' => '2',
    'BUSINESS_RECORD_PER_PAGE' => '10',

    'OWNER_THUMBNAIL_IMAGE_PATH' => 'owner/thumbnail/',
    'OWNER_ORIGINAL_IMAGE_PATH' => 'owner/original/',
    'OWNER_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'OWNER_THUMBNAIL_IMAGE_WIDTH' => '60',
    'SMS_API_KEY' => 'DQ1kB0BIoke5HSFvBGjguQ',
    'INDIA_CODE' => '+91',

    //   Country flag
    'COUNTRY_FLAG_IMAGE_PATH' => 'country/',
    'COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH' => '60',
    'COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT' => '60',

    //subscription plan icon
    'SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH' => 'subscription_plan/original/',
    'SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH' => 'subscription_plan/thumbnail/',
    'SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH' => '60',

    'PREMIUM_ICON_IMAGE' => 'images/premium.png',
    'BASIC_ICON_IMAGE' => 'images/basic.png',
    'BASIC_ICON_IMAGE_BLUE' => 'images/arrow-blue.png',
    'BASIC_ICON_IMAGE_YELLOW' => 'images/arrow-yello.png',
    'BASIC_ICON_IMAGE_ORANGE' => 'images/arrrow-orange.png',
    'LIFETIME_PREMIUM_ICON_IMAGE' => 'images/lifetime_premium.png',

	'BASIC_MEMBERSHIP_MESSAGE' => 'You are currently on Basic(Free) Membership. Support Ryuva Club by applying for Premium Membership and create your Free Public Business Website.',

    'FCM_KEY' => env('FCM_KEY'),
    'RYUVA_CLUB_EMAIL_ID' => 'rana@ryuva.club',

    /** Advertisement  */
    'ADVERTISEMENT_ORIGINAL_IMAGE_PATH' => 'ads/original/',
    'ADVERTISEMENT_THUMBNAIL_IMAGE_PATH' => 'ads/thumbnail/',
    'ADVERTISEMENT_THUMBNAIL_IMAGE_HEIGHT' => '60',
    'ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH' => '60',

    'ADVERTISEMENT_STORAGE_TYPE' => 's3',

    'ADVERTISEMENT_PENDING_FALG' => 0,
    'ADVERTISEMENT_APPROVED_FALG' => 1,    
    'ADVERTISEMENT_REJECTED_FALG' => 2,

    'ADVERTISEMENT_BUY_TYPE_ID' => 0,   
    'ADVERTISEMENT_SELL_TYPE_ID' => 1,     
    'ADVERTISEMENT_SERVICE_TYPE_ID' => 2,

    'ADVERTISEMENT_BUY_TYPE' => 'Buy',   
    'ADVERTISEMENT_SELL_TYPE' => 'Sell',     
    'ADVERTISEMENT_SERVICE_TYPE' => 'Service',

    /** Send Enquiry */
    'CHAT_SEND_ENQUIRY_THREAD_TYPE' => 1,     

    /** Investment opportunity  */
    'CHAT_INVESTMENT_OPPORTUNITY_THREAD_TYPE' => 2,   

    /** Send Inquiry to Customer */  
    'CHAT_SEND_ENQUIRY_TO_CUSTOMER_THREAD_TYPE' => 3,    

    /** Ads Interest */ 
    'CHAT_ADS_INTEREST_THREAD_TYPE' => 4,     

    'membershipRequestSubject' => env('APP_NAME','RYEC Club').' - New Membership Request',
    // use for send bulk email
    'MAIL_TO' => env('MAIL_TO','info@ryuva.club'),
    'REPLY_TO' => env('REPLY_TO','info@ryuva.club'),

    // use for send SMS
    'SMS_USER_NAME' => env('SMS_USER_NAME','myrajasthanclub'),
    'SMS_SENDER_NAME' => env('SMS_SENDER_NAME','MPSMOT'),
    'SMS_TYPE' => env('SMS_TYPE','TRANS'),
    'SMS_API_KEY' => env('SMS_API_KEY','570274ed-bad2-4783-a126-39f8d3d4794d'),
    // Failure to send SMS from first id will use second id
    'SMS_USER_NAME2' => env('SMS_USER_NAME2','ryuvaa'),
    'SMS_SENDER_NAME2' => env('SMS_SENDER_NAME2','otpsms'),
    'SMS_TYPE2' => env('SMS_TYPE2','TRANS'),
    'SMS_API_KEY2' => env('SMS_API_KEY2','10306e72-102c-435e-8083-9f278a0c4f0a'),
    'QrCode_PATH' => 'QrCode/',
    'HI' => 'हिंदी',
    'RJ' => 'राजस्थानी',

    //  Icon image URL
    'ICON_IMAGE_PATH' => 'icon/',
    //- Jobs
    'JOB_IMAGE_PATH' => 'jobs/',
    //- Jobs Applied
    'JOB_APPLY_IMAGE_PATH' => 'jobs/apply/'
    
];
