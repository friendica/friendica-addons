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
    butrem.value = '$remove_t';
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
  </script>
  <h2>$title</h2>
  <p><a href="$help">Get Help</a></p>
  <form method="post">
    <input type="hidden" name="id" value="$id">
{{ inc field_checkbox.tpl with $field=$enable }}{{ endinc }}
{{ inc field_input.tpl with $field=$pattern }}{{ endinc }}
{{ inc field_input.tpl with $field=$replace }}{{ endinc }}
{{ inc field_checkbox.tpl with $field=$images }}{{ endinc }}
{{ inc field_input.tpl with $field=$retrospective }}{{ endinc }}
    <h3>$include_t:</h3>
    <div>
      <table>
        <thead>
          <tr><th>$tag_t</th><th>$attribute_t</th><th>$value_t</th></tr>
        </thead>
        <tbody id="retriever-include">
{{ if $include }}
  {{ for $include as $k=>$m }}
          <tr id="retriever-include-$k">
            <td><input name="retriever-include-$k-element" value="$m.element"></td>
            <td><input name="retriever-include-$k-attribute" value="$m.attribute"></td>
            <td><input name="retriever-include-$k-value" value="$m.value"></td>
            <td><input id="retrieve-include-$k-rem" type="button" onclick="retriever_remove_row('retriever-include', $k)" value="$remove_t"></td>
          </tr>
  {{ endfor }}
{{ else }}
          <tr id="retriever-include-0">
            <td><input name="retriever-include-0-element"></td>
            <td><input name="retriever-include-0-attribute"></td>
            <td><input name="retriever-include-0-value"></td>
            <td><input id="retrieve-include-0-rem" type="button" onclick="retriever_remove_row('retriever-include', 0)" value="$remove_t"></td>
          </tr>
{{ endif }}
        </tbody>
      </table>
      <input type="button" onclick="retriever_add_row('retriever-include')" value="$add_t">
    </div>
    <h3>$exclude_t:</h3>
    <div>
      <table>
        <thead>
          <tr><th>Tag</th><th>Attribute</th><th>Value</th></tr>
        </thead>
        <tbody id="retriever-exclude">
{{ if $exclude }}
  {{ for $exclude as $k=>$r }}
          <tr id="retriever-exclude-$k">
            <td><input name="retriever-exclude-$k-element" value="$r.element"></td>
            <td><input name="retriever-exclude-$k-attribute" value="$r.attribute"></td>
            <td><input name="retriever-exclude-$k-value" value="$r.value"></td>
            <td><input id="retrieve-exclude-$k-rem" type="button" onclick="retriever_remove_row('retriever-exclude', $k)" value="$remove_t"></td>
          </tr>
  {{ endfor }}
{{ else }}
          <tr id="retriever-exclude-0">
            <td><input name="retriever-exclude-0-element"></td>
            <td><input name="retriever-exclude-0-attribute"></td>
            <td><input name="retriever-exclude-0-value"></td>
            <td><input id="retrieve-exclude-0-rem" type="button" onclick="retriever_remove_row('retriever-exclude', 0)" value="$remove_t"></td>
          </tr>
{{ endif }}
        </tbody>
      </table>
      <input type="button" onclick="retriever_add_row('retriever-exclude')" value="$add_t">
    </div>
    <input type="submit" size="70" value="$submit">
  </form>
</div>
