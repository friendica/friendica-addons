<div class="mailstream-item-body">$item.body</div>
{{ if $item.plink }}
<div>$upstream: <a class="mailstream-item-plink" href="$item.plink">$item.plink</a><div>
<div>$local: <a class="mailstream-item-url" href="$item.url">$item.url</a></div>
{{ endif }}
