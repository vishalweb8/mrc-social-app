<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
/**
 * PayGIntegration Base Controller
 *
 * Class PayGIntegration
 * 
 */
class PayGIntegration extends Model {

	/** 

	  Staging Information 

	**/ 

	private  $paymentURL;
	private  $AuthenticationKey;
	private  $AuthenticationToken;
	private  $SecureHashKey;
	private  $MerchantKeyId;
	private  $RedirectUrlWeb;
	private  $RedirectUrlMobile;

	function __construct(){
		/** @var string This Request URL */
		$this->paymentURL = config('app.Payment_URL'); 
		/** @var string AuthenticationKey For Payment Provided By Gateway */
		$this->AuthenticationKey = config('app.Authentication_Key'); 
		/** @var string AuthenticationToken For Payment Provided By Gateway */
		$this->AuthenticationToken  =config('app.Authentication_Token');
		/** @var  string SecureHashKey For Payment Provided By Gateway */
		$this->SecureHashKey  = config('app.SecureHash_Key');
		/** @var  string MerchantKeyId For Payment Provided By Gateway. */
		$this->MerchantKeyId = config('app.MerchantKey_Id');
	 	/** @var  string RedirectUrl For CallBack Url. */
		//private  $RedirectUrl = 'http://myrajasthan.club/home/payment-successful';
		$this->RedirectUrlWeb = config('app.Redirect_Url_Web');

		//\Log::info("Web Pay Payment User Data:".$this->RedirectUrlWeb);

		$this->RedirectUrlMobile = config('app.Redirect_Url_Mobile');
		/** @var  Integer Time Out For Curl Session. */
	}
	/** 
	  Live Information 

	**/ 
	
	// /** @var string This Request URL */
	// private  $paymentURL = 'https://uatapi.payg.in/Payment/Api/order'; 
	// /** @var string AuthenticationKey For Payment Provided By Gateway */
	// private  $AuthenticationKey = '9f44e4369cb64e6eb37607f7cde64e5c'; 
	// /** @var string AuthenticationToken For Payment Provided By Gateway */
	// private  $AuthenticationToken  ='5d86219deaf84c0bbce1c3deb4d7a539';
	// * @var  string SecureHashKey For Payment Provided By Gateway 
	// private  $SecureHashKey  = 'b15970db811f4b7c9d5ae3f26a0cb145';
	// /** @var  string MerchantKeyId For Payment Provided By Gateway. */
	// private  $MerchantKeyId = ' 7990';
 // 	/** @var  string RedirectUrl For CallBack Url. */
	// //private  $RedirectUrl = 'http://myrajasthan.club/home/payment-successful';
	// private  $RedirectUrl = 'https://ryuva.club/home/payment-successful';
	// /** @var  Integer Time Out For Curl Session. */


	private $timeout = 30;
	
	/**
	 * Sets the genrate random string 10 charactor (mandatory).
	 *
	 * @param string $length Default 10 charactor .
	 * @return 10 charactor radom string 
	 */

	public static function generateRandomString($length = 10) {

		/*$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;*/
		

    // md5 the timestamps and returns substring 
    // of specified length 
		return substr(md5(time()), 0, $length);
	}
	
	

	/**
	 * Sets Create new customer order for request
	 *
	 * @param PayGIntegration – <MerchantAuthenticationKey>:<MerchantAuthenticationToken>:M:<MerchantKeyId> in base64_encode.
	 * Required Fields 
	 *	Merchantkeyid integer (10) Id which is obtained on MerchantRegistration Mandatory
	 *	UniqueRequestId string(10) Unique Id generated for that particular Request Mandatory
	 *	OrderAmount decimal(18,2) Transaction Amount Mandatory
	 * @return Response Json Object With OrderKeyId and Payment Url
	 */
	public function orderCreateAndroid($postdata,$userId = null,$redirect_url =null) {
			/**
	 			* Set Form data in array to pass in request
	 		*/
			$arrData = array (
			  'MerchantKeyId' => $this->MerchantKeyId,
			  'UniqueRequestId' => PayGIntegration::generateRandomString(),
			  'OrderAmount' => $postdata['OrderAmount'],
			  'OrderType' => $postdata['OrderType'],
			  'OrderStatus' => 'Initiating',
			  'CustomerData' => 
			  array (
				'CustomerId' => $postdata['CustomerData']['CustomerId'],
				'CustomerNotes' => '',
				'FirstName' => $postdata['CustomerData']['FirstName'],
				'LastName' => $postdata['CustomerData']['LastName'],
				'MobileNo' => $postdata['CustomerData']['MobileNo'],
				'Email' => $postdata['CustomerData']['Email'],
				'EmailReceipt' => false,
				'BillingAddress' => '',
				'BillingCity' => '',
				'BillingState' => '',
				'BillingCountry' => '',
				'BillingZipCode' => '',
				'ShippingFirstName' => '',
				'ShippingLastName' => '',
				'ShippingAddress' => '',
				'ShippingCity' => '',
				'ShippingState' => '',
				'ShippingCountry' => '',
				'ShippingZipCode' => '',
				'ShippingMobileNo' => '',
			  ),
			  'UserDefinedData' => 
			  array (
				'UserDefined1' => '',
				'UserDefined2' => '',
				'UserDefined3' => '',
				'UserDefined4' => '',
				'UserDefined5' => '',
				'UserDefined6' => '',
				'UserDefined7' => '',
				'UserDefined8' => '',
				'UserDefined9' => '',
				'UserDefined10' => '',
				'UserDefined11' => '',
				'UserDefined12' => '',
				'UserDefined13' => '',
				'UserDefined14' => '',
				'UserDefined15' => '',
				'UserDefined16' => '',
				'UserDefined17' => '',
				'UserDefined18' => '',
				'UserDefined19' => '',
				'UserDefined20' => '',
			  ),
			  'IntegrationData' => 
			  array (
				'UserName' => '',
				'Source' => $postdata['Source'],
				'IntegrationType' => $postdata['IntegrationType'],
				'HashData' => '',
				'PlatformId' => '',
			  ),
			  'RecurringBillingData' => '',
			  'CouponData' => '',
			  'ShipmentData' => '',
			  'RequestDateTime' => date('mdY'),
			  'RedirectUrl' => $this->RedirectUrlMobile,
			  'Source' => '',
			);
			
			$header = array(
		    
		    'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->AuthenticationKey.":".$this->AuthenticationToken.":M:".$this->MerchantKeyId)
			);
			//form data json encode 
			$arrDatajson = json_encode($arrData);
			//form data json encode 
			$arrDatajson = json_encode($arrData);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => $this->paymentURL.'/create',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS =>$arrDatajson,
			CURLOPT_HTTPHEADER => $header,
			));
			$response = curl_exec($curl);
			if(curl_errno($curl)){
				throw new \Exception(curl_error($curl));
			}
			curl_close($curl);
			return  $response;
		
	}
	

		/**
	 * Sets Create new customer order for request
	 *
	 * @param PayGIntegration – <MerchantAuthenticationKey>:<MerchantAuthenticationToken>:M:<MerchantKeyId> in base64_encode.
	 * Required Fields 
	 *	Merchantkeyid integer (10) Id which is obtained on MerchantRegistration Mandatory
	 *	UniqueRequestId string(10) Unique Id generated for that particular Request Mandatory
	 *	OrderAmount decimal(18,2) Transaction Amount Mandatory
	 * @return Response Json Object With OrderKeyId and Payment Url
	 */
	public function orderCreateWeb($postdata,$userId = null,$redirect_url =null) {
			/**
	 			* Set Form data in array to pass in request
	 		*/
			
			$arrData = array (
			  'MerchantKeyId' => $this->MerchantKeyId,
			  'UniqueRequestId' => PayGIntegration::generateRandomString(),
			  'OrderAmount' => $postdata['OrderAmount'],
			  'OrderType' => $postdata['OrderType'],
			  'OrderStatus' => 'Initiating',
			  'CustomerData' => 
			  array (
				'CustomerId' => $postdata['CustomerData']['CustomerId'],
				'CustomerNotes' => '',
				'FirstName' => $postdata['CustomerData']['FirstName'],
				'LastName' => $postdata['CustomerData']['LastName'],
				'MobileNo' => $postdata['CustomerData']['MobileNo'],
				'Email' => $postdata['CustomerData']['Email'],
				'EmailReceipt' => false,
				'BillingAddress' => '',
				'BillingCity' => '',
				'BillingState' => '',
				'BillingCountry' => '',
				'BillingZipCode' => '',
				'ShippingFirstName' => '',
				'ShippingLastName' => '',
				'ShippingAddress' => '',
				'ShippingCity' => '',
				'ShippingState' => '',
				'ShippingCountry' => '',
				'ShippingZipCode' => '',
				'ShippingMobileNo' => '',
			  ),
			  'UserDefinedData' => 
			  array (
				'UserDefined1' => '',
				'UserDefined2' => '',
				'UserDefined3' => '',
				'UserDefined4' => '',
				'UserDefined5' => '',
				'UserDefined6' => '',
				'UserDefined7' => '',
				'UserDefined8' => '',
				'UserDefined9' => '',
				'UserDefined10' => '',
				'UserDefined11' => '',
				'UserDefined12' => '',
				'UserDefined13' => '',
				'UserDefined14' => '',
				'UserDefined15' => '',
				'UserDefined16' => '',
				'UserDefined17' => '',
				'UserDefined18' => '',
				'UserDefined19' => '',
				'UserDefined20' => '',
			  ),
			  'IntegrationData' => 
			  array (
				'UserName' => '',
				'Source' => $postdata['Source'],
				'IntegrationType' => $postdata['IntegrationType'],
				'HashData' => '',
				'PlatformId' => '',
			  ),
			  'RecurringBillingData' => '',
			  'CouponData' => '',
			  'ShipmentData' => '',
			  'RequestDateTime' => date('mdY'),
			  'RedirectUrl' => $this->RedirectUrlWeb.'/'.$userId,
			  'Source' => '',
			);
			
			$header = array(
		    
		    'Content-Type: application/json',
		    'Authorization: Basic '. base64_encode($this->AuthenticationKey.":".$this->AuthenticationToken.":M:".$this->MerchantKeyId)
		);
		//form data json encode 
		$arrDatajson = json_encode($arrData);
		//form data json encode 
		$arrDatajson = json_encode($arrData);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->paymentURL.'/create',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS =>$arrDatajson,
		CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		if(curl_errno($curl)){
       		throw new \Exception(curl_error($curl));
   		}
		curl_close($curl);
		return  $response;
		
	}
	

	/**
	 * Sets Create new customer order for request
	 *
	 * @param PayGIntegration – <MerchantAuthenticationKey>:<MerchantAuthenticationToken>:M:<MerchantKeyId> in base64_encode.
	 * Required Fields 
	 *	Merchantkeyid integer (10) Id which is obtained on MerchantRegistration Mandatory
	 *	UniqueRequestId string(10) Unique Id generated for that particular Request Mandatory
	 *	OrderAmount decimal(18,2) Transaction Amount Mandatory
	 * @return Response Json Object With OrderKeyId and Payment Url
	 */

	public function orderCreate($postdata,$redirectUrl=null,$paymentType='') {
		
			
			/**
	 			* Set Form data in array to pass in request
	 		*/
			$arrData  = array(
				'Merchantkeyid' => $this->MerchantKeyId,
				'UniqueRequestId'=>PayGIntegration::generateRandomString(),
				'UserDefinedData'=>array('UserDefined1' =>'' ),
			 	//'IntegrationData'=> array('UserName' => 'JoeSmith','Source'=>'','IntegrationType'=>'','HashData'=>'','PlatformId'=>'' ),
			 	'RequestDateTime'=> date('mdY'),
				//'RedirectUrl' => $this->RedirectUrlWeb.'/'.$userId,
				'RedirectUrl' => $redirectUrl,
                'OrderStatus' => 'Initiating',
				'TransactionData'=> array(
					'AcceptedPaymentTypes' =>'' ,
					'PaymentType'=> ($paymentType == 'upi') ? 'UPIINTENT' : '', 
					'SurchargeType'=>'',
					'SurchargeValue'=>'',
					'RefTransactionId'=>'',
					'IndustrySpecificationCode'=>'',
					'PartialPaymentOption'=>''
				 ),

			);

			/**
	 			* Set Form data in array to pass in request
	 		*/
			foreach ($postdata as $key => $keyval) {
				if($key == 'CustomerData'){
					foreach ($keyval as $cust_key => $cust_keyval) {
						$arrData[$key][$cust_key] = $cust_keyval;
					}
				}
			/**
	 			* Set Order Amount Data 
	 		*/
				if($key == 'OrderAmountData'){
					foreach ($keyval as $orderamount_key => $orderamount_keyval) {
						$arrData[$key][$orderamount_key] = $orderamount_keyval;
					}
				}
			/**
	 			* Set Integration data in array to pass in request
	 		*/	
				if($key == 'IntegrationData'){
					foreach ($keyval as $integrationdata_key => $integrationdata_keyval) {
						$arrData[$key][$integrationdata_key] = $integrationdata_keyval;
					}
				}

				
				$arrData[$key] = $keyval;
			}

		
		$header = array(
		    
		    'Content-Type: application/json',
		    'Authorization: Basic '. base64_encode($this->AuthenticationKey.":".$this->AuthenticationToken.":M:".$this->MerchantKeyId)
		);
		//form data json encode 
        info("order request");
        info($arrData);
		$arrDatajson = json_encode($arrData);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->paymentURL.'/create',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS =>$arrDatajson,
		CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		if(curl_errno($curl)){
       		throw new \Exception(curl_error($curl));
   		}
		curl_close($curl);
		return  $response;
		
	}

	public  function orderUpdate($postdata,$orderKeyID)
	{
		$arrData  = array(
				'Merchantkeyid' => $this->MerchantKeyId,
				'UniqueRequestId'=>PayGIntegration::generateRandomString(),
				'OrderKeyId'=>$orderKeyID,
				'UserDefinedData'=>array('UserDefined1' =>'' ),
			 	'
			 	'=> array('UserName' => 'JoeSmith','Source'=>'','IntegrationType'=>'','HashData'=>'','PlatformId'=>'' ),
			 	'RequestDateTime'=>'09212020',
				'RedirectUrl' => 'https://a2zfame.com',
				'TransactionData'=> array(
					'AcceptedPaymentTypes' =>'' ,
					'PaymentType'=>'',
					'SurchargeType'=>'',
					'SurchargeValue'=>'',
					'RefTransactionId'=>'',
					'IndustrySpecificationCode'=>'',
					'PartialPaymentOption'=>''
				 ),

			);
			/**
	 			* Set Form data in array to pass in request
	 		*/
			foreach ($postdata as $key => $keyval) {
				if($key == 'CustomerData'){
					foreach ($keyval as $cust_key => $cust_keyval) {
						$arrData[$key][$cust_key] = $cust_keyval;
					}
				}
				$arrData[$key] = $keyval;
			}
		$header = array(
		    
		    'Content-Type: application/json',
		    'Authorization: Basic '. base64_encode($this->AuthenticationKey.":".$this->AuthenticationToken.":M:".$this->MerchantKeyId)
		);	
		$arrDatajson = json_encode($arrData);	
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $this->paymentURL.'/Update',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS =>$arrDatajson,
		  CURLOPT_HTTPHEADER => $header,
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return  $response;
		
	}

	public  function orderDetail($OrderKeyId = null ,$PaymentTransactionId = null,$PaymentType = null)
	{
		$header = array(
		    'Content-Type: application/json',
		    'Authorization: Basic '. base64_encode($this->AuthenticationKey.":".$this->AuthenticationToken.":M:".$this->MerchantKeyId)
			);
		
		$arrData = array('OrderKeyId' =>$OrderKeyId,'MerchantKeyId'=>$this->MerchantKeyId,'PaymentType'=>$PaymentType);

		$arrDatajson = json_encode($arrData);
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $this->paymentURL.'/Detail',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS =>$arrDatajson,
		  CURLOPT_HTTPHEADER => $header,
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;		
				
	}

}
