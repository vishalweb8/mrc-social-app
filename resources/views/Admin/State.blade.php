<div class="form-group">
    <label for="category_id" class="col-sm-2 control-label" ></label>
    <div class="col-sm-8">
        <!-- <select name="categoryArray[]" id="subcategory" class="form-control" onchange="return getSubCategory(this, {{$level+1}});" multiple>
            @forelse($categoryArray as $category)
                <option class="type_parent_cat cat_type" value="{{$category->id}}" >{{$category->name}}</option>
            @empty
            @endforelse
        </select> -->
        <ol class="breadcrumb">
            @forelse($categoryHierarchy as $cat)
                <li class="active"><a href="javascript:getParentSubCategory({{$cat['id']}}, {{$level+1}})">{{$cat['name']}}</a></li>
            @empty
            @endforelse
        </ol>
        </div>
</div>


