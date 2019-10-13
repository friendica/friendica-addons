<?xml version="1.0" encoding="utf-8"?>

<!-- attempt to replace relative URLs with absolute URLs -->
<!-- http://stackoverflow.com/questions/3824631/replace-href-value-in-anchor-tags-of-html-using-xslt -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" indent="yes" version="4.0"/>

  <xsl:template match="node()|@*">
    <xsl:copy>
      <xsl:apply-templates select="node()|@*"/>
    </xsl:copy>
  </xsl:template>

  <xsl:template match="*/@src[starts-with(.,'.')]">
    <xsl:attribute name="src">
      <xsl:value-of select="concat('{{$dirurl}}',.)"/>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="*/@src[starts-with(.,'/')]">
    <xsl:attribute name="src">
      <xsl:value-of select="concat('{{$rooturl}}',.)"/>
    </xsl:attribute>
  </xsl:template>

</xsl:stylesheet>
