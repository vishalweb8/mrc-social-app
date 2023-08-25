<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{trans('labels.appname')}}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css')}}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css')}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('css/ionicons.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/AdminLTE.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/skins/skin-purple-light.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{ asset('css/custom.css')}}">
    <link rel="stylesheet" href="{{ asset('plugins/datepicker/datepicker3.css')}}">
    <!-- Datatables style -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables/dataTables.bootstrap.css')}}">
    <link rel="stylesheet" href="{{ asset('plugins/iCheck/all.css')}}">

    @yield('header')
</head>
@if (Auth::check())

<body class="hold-transition skin-purple-light sidebar-mini">
    @else

    <body class="hold-transition login-page">
        @endif

        <div class="wrapper">
            @if (Auth::check())
            <header class="main-header">
                <!-- Logo -->
                <a href="{{ url('/')}}" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini">{{trans('labels.appshortname')}}</span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg">{{trans('labels.appname')}}</span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">{{trans('labels.togglenav')}}</span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">

                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="{{ asset('/images/avatar5.png')}}" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{Auth::user()->name}}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="{{ asset('/images/avatar5.png')}}" class="img-circle" alt="User Image">
                                        <p>
                                            {{Auth::user()->name}}
                                        </p>
                                    </li>

                                    <li class="user-footer">
                                        <div style="text-align: center;">
                                            <a href="{{ url('admin/logout')}}" class="btn btn-default btn-flat">{{trans('labels.logout')}}</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{ asset('/images/avatar5.png')}}" class="img-circle" alt="User Image">
                        </div>
                        <div class="pull-left info">
                            <p>{{Auth::user()->name}}</p>
                        </div>
                    </div>

                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <?php
                        if (isset($controller) && !empty($controller));
                        else
                            $controller = '';
                        ?>

                        @if(Auth::user()->agent_approved == 1)
                        <li <?php if (in_array(Route::current()->uri(), ['admin/users', 'admin/adduser', 'admin/edituser/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/users') }}">
                                <i class="fa fa-users"></i> <span>{{trans('labels.users')}}</span>
                            </a>
                        </li>
                        <li <?php if (in_array(Route::current()->uri(), ['admin/allentity'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/allentity') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.businesses')}}</span>
                            </a>
                        </li>

                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/state', 'admin/country', 'admin/city', 'admin/editstate/{id}', 'admin/addstate', 'admin/addcity', 'admin/editcity/{id}', 'admin/addcountry', 'admin/editcountry/{id}', 'admin/location', 'admin/location/create', 'admin/location/edit/{id}'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-globe"></i> <span>Zones</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li <?php if (in_array(Route::current()->uri(), ['admin/country', 'admin/addcountry', 'admin/editcountry/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/country') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.country')}}</span>
                                    </a>
                                </li>

                                <li <?php if (in_array(Route::current()->uri(), ['admin/state', 'admin/editstate/{id}', 'admin/addstate'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/state') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.state')}}</span>
                                    </a>
                                </li>

                                <li <?php if (in_array(Route::current()->uri(), ['admin/city', 'admin/addcity', 'admin/editcity/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/city') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.city')}}</span>
                                    </a>
                                </li>
                                <li <?php if (in_array(Route::current()->uri(), ['admin/location', 'admin/location/create', 'admin/location/edit/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/location') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.location')}}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        @else

                        <li class="<?php if (Route::current()->uri() == 'admin/dashboard') {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/dashboard') }}">
                                <i class="fa fa-dashboard"></i> <span>{{trans('labels.dashboard')}}</span>
                            </a>
                        </li>
                        @can(config('perm.listMemberReq'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/membershiprequest'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/membershiprequest') }}">
                                <i class="fa fa-bullhorn"></i> <span>{{trans('labels.membershiprequest')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listClaim'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/entity-claim-request'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/entity-claim-request') }}">
                                <i class="fa fa-bullhorn"></i> <span>{{trans('labels.business_claim_request')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listUser'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/users', 'admin/adduser', 'admin/edituser/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/users') }}">
                                <i class="fa fa-users"></i> <span>{{trans('labels.users')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listOTP'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/otp', 'admin/editotp/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/otp') }}">
                                <i class="fa fa-users"></i> <span>{{trans('labels.otp')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listEntity'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/allentity', 'admin/show/entity/{id}', 'admin/entity/reports/view/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/allentity') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.entities')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listPremiumEntity'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/premiumbusiness'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/premiumbusiness') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.premiumbusinesses')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listSite'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/site','admin/site/{site}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/site') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.sites')}}</span>
                            </a>
                        </li>
                        @endcan

                        @canany([config('perm.listJobs'),config('perm.listJobApply')])
                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/job-apply-list', 'admin/jobs/create', 'admin/jobs/{jobs}/edit'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-user"></i> <span>Job Module</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                @can(config('perm.listJobs'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/jobs', 'admin/jobs/create', 'admin/jobs/{jobs}/edit'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/jobs') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.jobs')}}</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listJobApply'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/job-apply-list'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/job-apply-list') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.job_apply_list')}}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany


                        @can(config('perm.listAssetType'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/assetType', 'admin/assetType/create', 'admin/assetType/{assetType}/edit', 'admin/subAssetType/{id}', 'admin/create/subAssetType/{parent}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/assetType') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.asset_type')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listReason'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/reason', 'admin/reason/create', 'admin/reason/{reason}/edit'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/reason') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.reasons')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listReports'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/report', 'admin/report/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/report') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.reports')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listAdvertise'))
                        <li class="<?php if (in_array(Route::current()->getName(), ['businessBrandingInquiry.index'])) {
                                        echo 'active';
                                    } ?> ">
                            <a href="{{ route('businessBrandingInquiry.index') }}">
                                <i class="fa fa-image"></i>
                                <span>{{trans('labels.business_branding_inquiry')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listSearchTerm'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/searchterm'])) {
                                        echo 'active';
                                    } ?> ">
                            <a href="{{ url('admin/searchterm') }}">
                                <i class="fa fa-navicon"></i>
                                <span>{{trans('labels.searchterm')}}</span>
                            </a>
                        </li>
                        @endcan
                        @canany([config('perm.listPublicWebsite'),config('perm.listPublicWebsiteTemplate'),config('perm.listPublicWebsitePayments'),config('perm.listPublicWebsiteInquiry'),config('perm.listPublicWebsiteReview'),config('perm.listPublicWebsitePlan')])
                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/all-public-review', 'admin/all-public-inquiry', 'admin/allpublicwebsitetetemplets', 'admin/allpublicwebsiteplans', 'admin/allpublicwebsite', 'admin/allpublicwebsitepayments', 'admin/allpublicwebsite', 'admin/allpublicwebsitepayments'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-globe"></i> <span>Public Website</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                @can(config('perm.listPublicWebsiteTemplate'))
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/allpublicwebsitetetemplets'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ route('PublicWebsiteTetemplets.list') }}">
                                        <i class="fa fa-book"></i>
                                        <span>Public Website Templates</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPublicWebsitePlan'))
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/allpublicwebsiteplans'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ route('PublicWebsiteplans.list') }}">
                                        <i class="fa fa-paper-plane"></i>
                                        <span>Public Website Plans</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPublicWebsite'))
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/allpublicwebsite'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ route('publicwebsite.list') }}">
                                        <i class="fa fa-sitemap"></i>
                                        <span>Public Website</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPublicWebsitePayments'))
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/allpublicwebsitepayments'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ route('PublicWebsitepayments.list') }}">
                                        <i class="fa fa-credit-card"></i>
                                        <span>Public Website Payments</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPublicWebsiteInquiry'))
                                <li class="@if(Route::current()->uri() == 'admin/all-public-inquiry') active @endif treeview">
                                    <a href="{{ route('public.inquiry') }}">
                                        <i class="fa fa-question"></i>
                                        <span>Public Website Inquiry</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPublicWebsiteReview'))
                                <li class="@if(Route::current()->uri() == 'admin/all-public-review') active @endif treeview">
                                    <a href="{{ route('public.review') }}">
                                        <i class="fa fa-commenting-o"></i>
                                        <span>Public Website Review</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can(config('perm.listPromotedEntity'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/getAllPromotedBusinesses'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/getAllPromotedBusinesses') }}">
                                <i class="fa fa-bullhorn"></i> <span>{{trans('labels.promoted_business')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listMarketplaceAds'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/advertisement'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/advertisement') }}">
                                <i class="fa fa-navicon"></i> <span>{{trans('labels.marketplace')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listCategory'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/categories', 'admin/category/create', 'admin/category/edit/{id}', 'admin/category/subcategories/{parentId}', 'admin/category/subcategory/{parentId}/addsubcategory', 'category/subcategory/{parentId}/editsubcategory/{editId}'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/categories') }}">
                                <i class="fa fa-list-ul"></i>
                                <span>{{trans('labels.lblcategorymanagement')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listTrending'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/getAllTrendingCategory'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/getAllTrendingCategory') }}">
                                <i class="fa fa-area-chart"></i> <span>{{trans('labels.trending_categories')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listTrendingService'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/getAllTrendingServices'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/getAllTrendingServices') }}">
                                <i class="fa fa-line-chart"></i> <span>{{trans('labels.trending_services')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listRepresentativeRequests'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/agents'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/agents') }}">
                                <i class="fa fa-user-secret"></i> <span>{{trans('labels.agentrequest')}}</span>
                                <?php $agentrequestcount = Helpers::getPendingAgent(); ?>
                                @if($agentrequestcount > 0)
                                <span class="pull-right-container">
                                    <span class="label label-primary pull-right">{{$agentrequestcount}}</span>
                                </span>
                                @endif
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listMembershipPlan'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/subscriptions', 'admin/addsubscription', 'admin/editsubscription/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/subscriptions') }}">
                                <i class="fa fa-dashboard"></i> <span>{{trans('labels.membershipplans')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listEmailTemplate'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/templates', 'admin/addtemplate', 'admin/edittemplate/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/templates') }}">
                                <i class="fa fa-envelope-o"></i> <span>{{trans('labels.emailtemplates')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listSendEmail'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/sendMail'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/sendMail') }}">
                                <i class="fa fa-envelope-o"></i>
                                <span>Send Mail</span>
                            </a>
                        </li>
                        @endcan
                        
                        @can(config('perm.listApplication'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/application'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/application') }}">
                                <i class="fa fa-mobile"></i>
                                <span>{{trans('labels.application')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listCallToAction'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/calltoaction'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/calltoaction') }}">
                                <i class="fa fa-mobile"></i>
                                <span>{{trans('labels.callToAction')}}</span>
                            </a>
                        </li>
                        @endcan

                        @can(config('perm.listPublicWebsiteReview'))
                        @endcan
                        @canany([config('perm.listCountry'),config('perm.listState'),config('perm.listCity')])
                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/state', 'admin/country', 'admin/city', 'admin/editstate/{id}', 'admin/addstate', 'admin/addcity', 'admin/editcity/{id}', 'admin/addcountry', 'admin/editcountry/{id}', 'admin/location', 'admin/location/create', 'admin/location/edit/{id}'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-globe"></i> <span>Zones</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                @can(config('perm.listCountry'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/country', 'admin/addcountry', 'admin/editcountry/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/country') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.country')}}</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listState'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/state', 'admin/editstate/{id}', 'admin/addstate'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/state') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.state')}}</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listCity'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/city', 'admin/addcity', 'admin/editcity/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/city') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.city')}}</span>
                                    </a>
                                </li>
                                @endcan

                                @can(config('perm.listLocation'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/location', 'admin/location/create', 'admin/location/edit/{id}'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/location') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.location')}}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can(config('perm.listNotification'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/notification'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/notification') }}">
                                <i class="fa fa-bullhorn"></i> <span>{{trans('labels.notifications')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listCms'))
                        <li <?php if (in_array(Route::current()->uri(), ['admin/cms', 'admin/addcms', 'admin/editcms/{id}'])) {
                                echo 'class="active"';
                            } ?>>
                            <a href="{{ url('admin/cms') }}">
                                <i class="fa fa-pencil-square-o"></i> <span>{{trans('labels.cms')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listNewsletter'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/newsletter', 'admin/newsletter/create', 'admin/newsletter/edit/{id}'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/newsletter') }}">
                                <i class="fa fa-newspaper-o"></i>
                                <span>{{trans('labels.newsletter')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listBranding'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/branding'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/branding') }}">
                                <i class="fa fa-image"></i>
                                <span>{{trans('labels.branding')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listPaymentTransaction'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/transactions'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/transactions') }}">
                                <i class="fa fa-money"></i>
                                <span>{{trans('labels.transactions')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listPost'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/publicPost'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/publicPost') }}">
                                <i class="fa fa-comments"></i>
                                <span>Public Posts</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.csvImport'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/csvimport'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/csvimport') }}">
                                <i class="fa fa-money"></i>
                                <span>{{trans('labels.csvimport')}}</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listOnlineStore'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/onlineStore'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/onlineStore') }}">
                                <i class="fa fa-cog"></i>
                                <span>Online Store</span>
                            </a>
                        </li>
                        @endcan
                        @can(config('perm.listSetting'))
                        <li class="<?php if (in_array(Route::current()->uri(), ['admin/settings'])) {
                                        echo 'active';
                                    } ?> treeview">
                            <a href="{{ url('admin/settings') }}">
                                <i class="fa fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        @endcan
                        @canany([config('perm.listRole'),config('perm.listPermission')])
                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/role', 'admin/role/create', 'admin/role/{role}/edit', 'admin/permission', 'admin/permission/create', 'admin/permission/{permission}/edit'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-user"></i> <span>Role and Permission</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                @can(config('perm.listRole'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/role', 'admin/role/create', 'admin/role/{role}/edit'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/role') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.roles')}}</span>
                                    </a>
                                </li>
                                @endcan
                                @can(config('perm.listPermission'))
                                <li <?php if (in_array(Route::current()->uri(), ['admin/permission', 'admin/permission/create', 'admin/permission/{permission}/edit'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/permission') }}">
                                        <i class="fa fa-align-justify"></i> <span>{{trans('labels.permissions')}}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can(config('perm.listNotInUse'))
                        <li class="<?php
                                    if (in_array(Route::current()->uri(), ['admin/plasmaDonor', 'admin/plasmaDonor/create', 'admin/plasmaDonor/{plasmaDonor}/edit', 'admin/investmentideas', 'admin/analytics'])) {
                                        echo 'active';
                                    }
                                    ?>  treeview">
                            <a href="">
                                <i class="fa fa-user"></i> <span>Not in use</span><i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">

                                @can(config('perm.listAdvisors'))
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/advisor'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ url('admin/advisor') }}">
                                        <i class="fa fa-tripadvisor"></i>
                                        <span>{{trans('labels.advisors')}}</span>
                                    </a>
                                </li>
                                @endcan
                                <li class="<?php if (in_array(Route::current()->uri(), ['admin/plasmaDonor', 'admin/plasmaDonor/create', 'admin/plasmaDonor/{plasmaDonor}/edit', 'admin/analytics'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ url('admin/plasmaDonor') }}">
                                        <i class="fa fa-heartbeat" aria-hidden="true"></i>
                                        <span>Plasma Donors</span>
                                    </a>
                                </li>
                                <li <?php if (in_array(Route::current()->uri(), ['admin/investmentideas'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/investmentideas') }}">
                                        <i class="fa fa-money"></i> <span>{{trans('labels.investment_opportunities')}}</span>
                                    </a>
                                </li>
                                <li <?php if (in_array(Route::current()->uri(), ['admin/analytics'])) {
                                        echo 'class="active"';
                                    } ?>>
                                    <a href="{{ url('admin/analytics') }}">
                                        <i class="fa fa-bar-chart-o"></i> <span>{{trans('labels.analytics')}}</span>
                                    </a>
                                </li>
                                <li class="<?php if (in_array(Route::current()->getName(), ['businessBranding.index'])) {
                                                echo 'active';
                                            } ?> treeview">
                                    <a href="{{ route('businessBranding.index') }}">
                                        <i class="fa fa-image"></i>
                                        <span>{{trans('labels.business_branding')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endcan

                        @endif

                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            @endif

            @if (Auth::check())
            <div class="content-wrapper">
                <div class="ajex-message">
                </div>

                @if ($message = Session::get('success'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.successlbl')}}</h4>
                                {{ $message }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($message = Session::get('error'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="alert alert-error alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">X</button>
                                <h4><i class="icon fa fa-check"></i> {{trans('validation.errorlbl')}}</h4>
                                {{ $message }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @yield('content')
            </div><!-- /.content-wrapper -->
            @else
            @yield('content')
            @endif

            @if (Auth::check())
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    {!! trans('labels.version') !!}
                </div>
                {!! trans('labels.copyrightstr') !!}
            </footer>
            @endif
            @yield('footer')
        </div>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script src="{{ asset('plugins/jQuery/jQuery-2.1.4.min.js')}}"></script>
        <!-- Bootstrap 3.3.5 -->
        <script src="{{ asset('js/bootstrap.min.js')}}"></script>
        <!-- SlimScroll -->
        <script src="{{ asset('plugins/slimScroll/jquery.slimscroll.min.js')}}"></script>
        <!-- FastClick -->
        <script src="{{ asset('plugins/fastclick/fastclick.min.js')}}"></script>
        <!-- Datepicker -->
        <script src="{{ asset('plugins/datepicker/bootstrap-datepicker.js')}}"></script>
        <!-- backendLTE App -->
        <script src="{{ asset('js/app.min.js')}}"></script>
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
        <!-- datatables -->
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{ asset('plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
        <script src="{{ asset('js/dataTables.hideEmptyColumns.js')}}"></script>
        <script src="{{ asset('plugins/iCheck/icheck.min.js')}}"></script>
        <script src="{{ asset('js/custom.js')}}"></script>


        @yield('script')
    </body>
</body>

</html>