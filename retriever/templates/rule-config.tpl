<div class="settings-block">
  <script language="javascript">
function retriever_add_row(id)
{
    var tbody = document.getElementById(id);
    var last = tbody.rows[tbody.childElementCount - 1];
    var count = +last.id.replace(id + '-', '');
    count++;
    var row = document.createElement('tr');
    row.id = id + '-' + count;
    var cell1 = document.createElement('td');
    var inptag = document.createElement('input');
    inptag.name = row.id + '-element';
    cell1.appendChild(inptag);
    row.appendChild(cell1);
    var cell2 = document.createElement('td');
    var inpatt = document.createElement('input');
    inpatt.name = row.id + '-attribute';
    cell2.appendChild(inpatt);
    row.appendChild(cell2);
    var cell3 = document.createElement('td');
    var inpval = document.createElement('input');
    inpval.name = row.id + '-value';
    cell3.appendChild(inpval);
    row.appendChild(cell3);
    var cell4 = document.createElement('td');
    var butrem = document.createElement('input');
    butrem.id = row.id + '-rem';
    butrem.type = 'button';
    butrem.onclick = function(){retriever_remove_row(id, count)};
    butrem.value = '{{$remove_t}}';
    cell4.appendChild(butrem);
    row.appendChild(cell4);
    tbody.appendChild(row);
}

function retriever_remove_row(id, number)
{
    var tbody = document.getElementById(id);
    var row = document.getElementById(id + '-' + number);
    tbody.removeChild(row);
}

function retriever_toggle_url_block()
{
    var pattern = document.querySelector("#id_retriever_pattern").parentNode;
    if (document.querySelector("#id_retriever_modurl").checked) {
        pattern.style.display = "block";
    }
    else {
        pattern.style.display = "none";
    }

    var replace = document.querySelector("#id_retriever_replace").parentNode;
    if (document.querySelector("#id_retriever_modurl").checked) {
        replace.style.display = "block";
    }
    else {
        replace.style.display = "none";
    }
}

function retriever_toggle_cookiedata_block()
{
    var div = document.querySelector("#id_retriever_cookiedata").parentNode;
    if (document.querySelector("#id_retriever_storecookies").checked) {
        div.style.display = "block";
    }
    else {
        div.style.display = "none";
    }
}

document.addEventListener('DOMContentLoaded', function() {
    retriever_toggle_url_block();
    document.querySelector("#id_retriever_modurl").addEventListener('change', retriever_toggle_url_block, false);
    retriever_toggle_cookiedata_block();
    document.querySelector("#id_retriever_storecookies").addEventListener('change', retriever_toggle_cookiedata_block, false);
}, false);
  </script>
  <h2>{{$title}}</h2>
  <p><a href="{{$help}}">{{$help_t}}</a></p>
  <form method="post">
    <input type="hidden" name="id" value="{{$id}}">
{{include file="field_checkbox.tpl" field=$enable}}
    <h3>{{$include_t}}:</h3>
    <div>
      <table>
        <thead>
          <tr><th>{{$tag_t}}</th><th>{{$attribute_t}}</th><th>{{$value_t}}</th></tr>
        </thead>
        <tbody id="retriever-include">
{{if $include}}
  {{foreach $include as $k=>$m}}
          <tr id="retriever-include-{{$k}}">
            <td><input name="retriever-include-{{$k}}-element" value="{{$m.element}}"></td>
            <td><input name="retriever-include-{{$k}}-attribute" value="{{$m.attribute}}"></td>
            <td><input name="retriever-include-{{$k}}-value" value="{{$m.value}}"></td>
            <td><input id="retrieve-include-{{$k}}-rem" type="button" onclick="retriever_remove_row('retriever-include', {{$k}})" value="{{$remove_t}}"></td>
          </tr>
  {{/foreach}}
{{else}}
          <tr id="retriever-include-0">
            <td><input name="retriever-include-0-element"></td>
            <td><input name="retriever-include-0-attribute"></td>
            <td><input name="retriever-include-0-value"></td>
            <td><input id="retrieve-include-0-rem" type="button" onclick="retriever_remove_row('retriever-include', 0)" value="{{$remove_t}}"></td>
          </tr>
{{/if}}
        </tbody>
      </table>
      <input type="button" onclick="retriever_add_row('retriever-include')" value="{{$add_t}}">
    </div>
    <h3>{{$exclude_t}}:</h3>
    <div>
      <table>
        <thead>
          <tr><th>{{$tag_t}}</th><th>{{$attribute_t}}</th><th>{{$value_t}}</th></tr>
        </thead>
        <tbody id="retriever-exclude">
{{if $exclude}}
  {{foreach $exclude as $k=>$r}}
          <tr id="retriever-exclude-{{$k}}">
            <td><input name="retriever-exclude-{{$k}}-element" value="{{$r.element}}"></td>
            <td><input name="retriever-exclude-{{$k}}-attribute" value="{{$r.attribute}}"></td>
            <td><input name="retriever-exclude-{{$k}}-value" value="{{$r.value}}"></td>
            <td><input id="retrieve-exclude-{{$k}}-rem" type="button" onclick="retriever_remove_row('retriever-exclude', {{$k}})" value="{{$remove_t}}"></td>
          </tr>
  {{/foreach}}
{{else}}
          <tr id="retriever-exclude-0">
            <td><input name="retriever-exclude-0-element"></td>
            <td><input name="retriever-exclude-0-attribute"></td>
            <td><input name="retriever-exclude-0-value"></td>
            <td><input id="retrieve-exclude-0-rem" type="button" onclick="retriever_remove_row('retriever-exclude', 0)" value="{{$remove_t}}"></td>
          </tr>
{{/if}}
        </tbody>
      </table>
      <input type="button" onclick="retriever_add_row('retriever-exclude')" value="{{$add_t}}">
    </div>
{{include file="field_checkbox.tpl" field=$modurl}}
{{include file="field_input.tpl" field=$pattern}}
{{include file="field_input.tpl" field=$replace}}
{{if $allow_images}}
{{include file="field_checkbox.tpl" field=$images}}
{{/if}}
{{include file="field_textarea.tpl" field=$customxslt}}
{{include file="field_checkbox.tpl" field=$storecookies}}
{{include file="field_textarea.tpl" field=$cookiedata}}
{{include file="field_input.tpl" field=$retrospective}}
    <input type="submit" size="70" value="{{$submit_t}}">
  </form>
</div>
