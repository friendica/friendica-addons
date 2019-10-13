<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" indent="yes" version="4.0"/>

  <xsl:template match="text()"/>
{{function clause_xpath}}{{if !$clause.attribute}}{{$clause.element}}{{elseif $clause.attribute == 'class'}}{{$clause.element}}[contains(concat(' ', normalize-space(@class), ' '), '{{$clause.value}}')]{{else}}{{$clause.element}}[@{{$clause.attribute}}='{{$clause.value}}']{{/if}}{{/function}}
{{foreach $spec.include as $clause}}

  <xsl:template match="{{clause_xpath clause=$clause}}">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*" mode="remove"/>
    </xsl:copy>
  </xsl:template>{{/foreach}}
{{foreach $spec.exclude as $clause}}

  <xsl:template match="{{clause_xpath clause=$clause}}" mode="remove"/>{{/foreach}}

  <xsl:template match="node()|@*" mode="remove">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*" mode="remove"/>
    </xsl:copy>
  </xsl:template>

</xsl:stylesheet>
