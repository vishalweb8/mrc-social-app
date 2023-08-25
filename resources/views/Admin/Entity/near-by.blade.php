<div class="near-by">
    <!-- <div class="form-group">
        <label for="title" class="col-sm-3 control-label">Is Enable Filters</label>
        <div class="col-sm-7">
            <label class="switch">
                <input class="near-by-switch" type="checkbox" name="is_enable_filter[0]"  autocomplete="off" value="1" @if(isset($nearByFilter) && $nearByFilter->is_enable_filter) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div> -->
    <div class="form-group">
        <?php
        if (isset($nearByFilter))
            $title = $nearByFilter->title;
        else
            $title = '';
        ?>
        <label for="title" class="col-sm-2 control-label">{{trans('labels.title')}}<span class="star_red">*</span></label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="title[]" placeholder="{{trans('labels.title')}}" value="{{$title}}">
        </div>
        @if($deleteBtn)
            <div class="col-sm-1" style="padding-left: 0px;"> <span style="cursor: pointer;" class="badge bg-red remove-near-by" ><i class="fa fa-remove"></i></span></div>
        @endif
    </div>

    <div class="form-group">
        <?php
        if (old('top_limit'))
            $top_limit = old('top_limit');
        elseif (isset($nearByFilter))
            $top_limit = $nearByFilter->top_limit;
        else
            $top_limit = 5;
        ?>
        <label for="top_limit" class="col-sm-2 control-label">Top limit</label>
        <div class="col-sm-8">
            <select name="top_limit[]" class="form-control" style="cursor: pointer;"  >
                <option value="5" @if($top_limit == 5) selected @endif>5</option>
                <option value="10" @if($top_limit == 10) selected @endif>10</option>
                <option value="15" @if($top_limit == 15) selected @endif>15</option>
                <option value="20" @if($top_limit == 20) selected @endif>20</option>
                
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <?php
        if (isset($nearByFilter) && !empty($nearByFilter->asset_type_id)) {
            $entity_types = explode(",",$nearByFilter->asset_type_id);
        } else {
            $entity_types = [];
        }
        ?>
        <label for="asset_type_id" class="col-sm-2 control-label">{{trans('labels.entities')}}</label>
        <div class="col-sm-8">                                
            <select  name="asset_type_id[0][]" class="form-control near-by-select2 select2" style="cursor: pointer;"  multiple>
                @foreach($allEntities as $entity)
                    <option value="{{$entity->id}}" @if(in_array($entity->id,$entity_types)) selected @endif>
                        {{$entity->name}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <h4>OR</h4>
    <div class="form-group">
        <?php

        if (old('sql_query'))
            $sql_query = old('sql_query');
        elseif (isset($nearByFilter))
            $sql_query = $nearByFilter->sql_query;
        else
            $sql_query = '';
        ?>                          
        <label for="sql_query" class="col-sm-2 control-label">SQL query</label>
        <div class="col-sm-8">
        <textarea type="text" class="form-control"  name="sql_query[]" placeholder="Please write sql query for where conditions">{{$sql_query}}</textarea>
        </div>
    </div>
</div>