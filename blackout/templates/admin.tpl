<div class="warning-message">{{$adminnote nofilter}}</div>

{{include file="field_input.tpl" field=$startdate}}
{{include file="field_input.tpl" field=$enddate}}
{{include file="field_input.tpl" field=$rurl}}

<div class="warning-message">{{$aboutredirect nofilter}}</div>

<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
