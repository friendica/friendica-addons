{{* We organize the settings in collapsable panel-groups *}}
{{* this div should be in frio theme *}}
<div class="panel-group panel-group-settings" id="securemail" role="tablist" aria-multiselectable="true">
    {{* The password setting section *}}
    <div class="panel">
        <div class="section-subtitle-wrapper" role="tab" id="securemail-settings">
            <h4>
                <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#settings" href="#securemail-settings-collapse" aria-expanded="true" aria-controls="securemail-settings-collapse">
                    {{$title}}
                </a>
            </h4>
        </div>
        <div id="securemail-settings-collapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="securemail-settings">
            <div class="section-content-tools-wrapper">
                {{include file="field_checkbox.tpl" field=$enable}}
                {{include file="field_textarea.tpl" field=$publickey}}

                <div class="form-group pull-right settings-submit-wrapper" >
                    <button type="submit" name="securemail-submit" class="btn btn-primary" value="{{$submit|escape:'html'}}">{{$submit}}</button>
                    <button type="submit" name="securemail-submit" class="btn btn-default" value="{{$test|escape:'html'}}">{{$test}}</button>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
