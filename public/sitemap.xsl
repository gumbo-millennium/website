<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9">

    <xsl:template match="/">
        <html>
            <head>
                <title>Sitemap</title>
            </head>
            <body>
                <h2>Sitemap</h2>
                <ul>
                    <xsl:for-each select="sm:urlset/sm:url">
                        <li>
                            <p>
                                <!-- link -->
                                    <a href="{sm:loc}">
                                        <xsl:value-of select="sm:loc" />
                                    </a>

                                <!-- priority, if set -->
                                <xsl:if test="sm:priority">
                                    <br />
                                    <span>Priority <xsl:value-of select="sm:priority" /></span>
                                </xsl:if>

                            <!-- last modified -->
                            <xsl:if test="sm:lastmod">
                                <br />
                                <span>Last Modified: <xsl:value-of select="sm:lastmod" /></span>
                            </xsl:if>
                            </p>
                        </li>
                    </xsl:for-each>
                </ul>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
