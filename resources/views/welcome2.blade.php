<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($categories as $category)
    <url>
        @if(!(strpos($category['type'], 'merchant')===false))
        <loc>{{url("/a/merchants/".$category['url'])}}</loc>
        @else
        <loc>{{url("/a/reports/".$category['url'])}}</loc>
        @endif
        <lastmod>{{ gmdate('Y-m-d\TH:i:s\Z',strtotime($category['updated_at'])) }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    @endforeach
    @foreach ($merchants as $merchant)
    <url>
        <loc>{{url("/a/merchant/".$merchant->slug)}}</loc>
        <lastmod>{{ gmdate('Y-m-d\TH:i:s\Z',strtotime($merchant->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
    @foreach ($merchant->cats as $cat)
    <url>
        <loc>{{url("/a/products/".$cat->url."?merchant_id=".$merchant->id)}}</loc>
        <lastmod>{{ gmdate('Y-m-d\TH:i:s\Z',strtotime($merchant->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
    @endforeach
    @foreach ($reports as $report)
    <url>
        <loc>{{url("/a/merchant/".$report->slug)}}</loc>
        <lastmod>{{ gmdate('Y-m-d\TH:i:s\Z',strtotime($report->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
    <url>
        <loc>{{url("/a/contact-us/vets")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/contact-us/shop")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/contact-us/lost")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/contact-us/sale")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/contact-us/bla")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/faq")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{url("/a/terms")}}</loc>
        <lastmod>2020-12-11T21:40:41Z</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
</urlset>
