{{include file="file:{{$field_input}}" field=$startdate}}
{{include file="file:{{$field_input}}" field=$enddate}}
{{include file="file:{{$field_input}}" field=$rurl}}

<div style="border: 2px solid #f00; padding: 10px; margin:
10px;font-size: 1.2em;"><strong>Note</strong>: The redirect will be active from the moment you
press the submit button. Users currently logged in will <strong>not</strong> be
thrown out but can't login again after logging out should the blackout is
still in place.</div>

<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
