<div class="box-header with-border">
    <h3 class="box-title">
        Enable/Disable fields for components
    </h3>
</div><!-- /.box-header -->
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label">Category</label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[category]"  autocomplete="off" value="1" @if(isset($components['category']) && $components['category']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Contact Details </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[contact_details]"  autocomplete="off" value="1" @if(isset($components['contact_details']) && $components['contact_details']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Social Profiles </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[social_profiles]"  autocomplete="off" value="1" @if(isset($components['social_profiles']) && $components['social_profiles']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>    
</div>
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label"> Online Stores </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[online_stores]"  autocomplete="off" value="1" @if(isset($components['online_stores']) && $components['online_stores']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label">  Hours of Operation(Timezone)  </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[hours_of_opration]"  autocomplete="off" value="1" @if(isset($components['hours_of_opration']) && $components['hours_of_opration']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label">  Business Activities  </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[business_activities]"  autocomplete="off" value="1" @if(isset($components['business_activities']) && $components['business_activities']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
</div>
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label">  Public Website  </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[public_website]"  autocomplete="off" value="1" @if(isset($components['public_website']) && $components['public_website']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Business Documents </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[business_documnet]"  autocomplete="off" value="1" @if(isset($components['business_documnet']) && $components['business_documnet']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Know More </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[know_more]"  autocomplete="off" value="1" @if(isset($components['know_more']) && $components['know_more']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
</div>
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label"> Owner Information  </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[owner_info]"  autocomplete="off" value="1" @if(isset($components['owner_info']) && $components['owner_info']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Products </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[product]"  autocomplete="off" value="1" @if(isset($components['product']) && $components['product']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Services </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[services]"  autocomplete="off" value="1" @if(isset($components['services']) && $components['services']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
</div>
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label">  Near By  </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[near_by]"  autocomplete="off" value="1" @if(isset($components['near_by']) && $components['near_by']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Review Rating </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[rating]"  autocomplete="off" value="1" @if(isset($components['rating']) && $components['rating']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <label for="name" class="col-sm-3 control-label"> Custom components </label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[custom_component]"  autocomplete="off" value="1" @if(isset($components['custom_component']) && $components['custom_component']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
</div>
<div class="row mt15">
    <label for="name" class="col-sm-3 control-label"> Video sharing</label>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" name="components[video]"  autocomplete="off" value="1" @if(isset($components['video']) && $components['video']) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
</div>