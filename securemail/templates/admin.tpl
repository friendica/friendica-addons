<span id="settings_securemail_inflated" class="settings-block fakelink" style="display: block" onclick="openClose('settings_securemail_expanded'); openClose('settings_securemail_inflated');">
  <h3>{{$title}}</h3>
</span>
<div id="settings_securemail_expanded" class="settings-block" style="display: none">
  <span class="fakelink" onclick="openClose('settings_securemail_expanded'); openClose('settings_securemail_inflated');"><h3>{{$title}}</h3></span>
  <div id="securemail-wrapper">
    {{include file="field_checkbox.tpl" field=$enable}}
    {{include file="field_textarea.tpl" field=$publickey}}

    <div class="form-group pull-right settings-submit-wrapper" >
      <button type="submit" name="securemail-submit" class="btn btn-primary" value="submit">{{$submit}}</button>
      <button type="submit" name="securemail-submit" class="btn btn-default" value="test">{{$test}}</button>
    </div>
    <div class="clear"></div>
  </div>
</div>
