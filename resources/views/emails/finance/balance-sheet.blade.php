<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td{
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .invoice-box table tr.footer {
            font-size: 13px !important;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        .rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
<div class="invoice-box" style="max-width: 800px;margin: auto;padding: 30px;border: 1px solid #eee;box-shadow: 0 0 10px rgba(0, 0, 0, .15);font-size: 16px;line-height: 24px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #555;">
    <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
        <tr class="top">
            <td colspan="3" style="padding: 5px;vertical-align: top;">
                <table style="width: 100%;line-height: inherit;text-align: left;">
                    <tr>
                        <td class="title" style="padding: 5px;vertical-align: top;padding-bottom: 20px;font-size: 45px;line-height: 45px;color: #333;">
                            <img src="{{ $headerLogo }}" style="width:100%; max-width:300px;">
                        </td>
                        <td style="padding: 5px;vertical-align: top;text-align: right;padding-bottom: 20px;">
                            <br>
                            <!-- Created: {{ $order->created_at->format('F d, Y') }} --><br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="information">
            <td colspan="3" style="padding: 5px;vertical-align: top;">
                <table style="width: 100%;line-height: inherit;text-align: left;">
                    <tr>
                        <td style="padding: 5px;vertical-align: top; padding-bottom: 40px;">
                            <h2>{{ $report_name }}</h2>
                            {{ $report_desc }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="heading">
            <td style="padding: 5px;vertical-align: top;background: #eee;border-bottom: 1px solid #ddd;font-weight: bold;"></td>
            <td style="padding: 5px;vertical-align: top;text-align: right;background: #eee;border-bottom: 1px solid #ddd;font-weight: bold;"></td>
            <td style="padding: 5px;vertical-align: top;text-align: right;background: #eee;border-bottom: 1px solid #ddd;font-weight: bold;">{{ $report["reportYears"]["to"] }}</td>
        </tr>
        <tr class="details">
            <td style="padding: 5px;vertical-align: top;padding-bottom: 20px;">&nbsp;</td>
            <td style="padding: 5px;vertical-align: top;padding-bottom: 20px; text-align: right;">&nbsp;</td>
            <td style="padding: 5px;vertical-align: top;padding-bottom: 20px; text-align: right;">NGN</td>
        </tr>
        <!-- <tr class="heading">
            <td colspan="3" style="padding: 5px;vertical-align: top;border-bottom: 1px solid #ddd;font-weight: bold;">&nbsp;</td>
        </tr> -->

        @if (count($headingAccounts) > 0)

            @foreach ($headingAccounts as $headingKey  => $headingValue)

                @php
                    $headingTotal = 0;
                @endphp

                <tr class="item" style="background: #cdcdcd; border: 2px solid #000;">
                    <td style="padding: 5px;vertical-align: top;border-bottom: 1px solid #efefef;"><strong>{{ strtoupper($headingValue["title"]) }}</strong></td>
                    <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #efefef;">&nbsp;</td>
                    <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #efefef;">&nbsp;</td>
                </tr>
                <!-- <tr class="heading">
                    <td colspan="3" style="padding: 5px; vertical-align: top; border-bottom: 1px solid #efefef;">&nbsp;</td>
                </tr> -->

                @if (count($headingValue["parentAccounts"]) > 0)
                    @foreach ($headingValue["parentAccounts"] as $parentKey  => $parentValue)

                        <tr class="item" style="background: #eee;">
                            <td style="padding: 5px;padding-left: 15px;vertical-align: top;border-bottom: 1px solid #eee;"><strong>{{ $parentValue["account"]["display_name"] }}</strong></td>
                            <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">&nbsp;</td>
                            <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">&nbsp;</td>
                        </tr>

                        @php
                            $sectionTotal = 0;
                        @endphp

                        @if (!empty($parentValue["sub_accounts"]))
                            @foreach ($parentValue["sub_accounts"] as $account)
                                @if ( $account["parent_account_id"] === $parentValue["account"]["id"] && !empty($reportSections[$parentKey]["accounts"]) && !empty($reportSections[$parentKey]['accounts'][$account["uuid"]]["totals"]) )
                                    <tr class="item" style="margin-left: 10px;">
                                        <td style="padding: 5px;padding-left: 30px;vertical-align: top;border-bottom: 1px solid #eee;">{{ $account["display_name"] }}</td>
                                        <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">&nbsp;</td>
                                        <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">{{ number_format($reportSections[$parentKey]['accounts'][$account["uuid"]]["totals"]) }}</td>
                                    </tr>
                                    @php
                                        $sectionTotal += $reportSections[$parentKey]['accounts'][$account["uuid"]]["totals"];
                                        $headingTotal += $sectionTotal;
                                    @endphp
                                @endif
                            @endforeach
                        @endif

                        <tr class="item" style="background: #eee;">
                            <td style="padding: 5px;padding-left: 15px;vertical-align: top;border-bottom: 1px solid #eee;"><strong>Total {{ ucwords($parentValue["account"]["display_name"]) }}</strong></td>
                            <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">&nbsp;</td>
                            <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;border-top: 2px solid #000;">{{ number_format($sectionTotal) }}</td>
                        </tr>

                        <tr class="heading">
                            <td colspan="3" style="padding: 5px; vertical-align: top; border-bottom: 1px solid #efefef;">&nbsp;</td>
                        </tr>

                    @endforeach
                @endif
                <tr class="item" style="background: #cdcdcd; border: 2px solid #000;">
                    <td style="padding: 5px;vertical-align: top;border-bottom: 1px solid #eee;"><strong>TOTAL {{ strtoupper($headingValue["title"]) }}</strong></td>
                    <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 1px solid #eee;">&nbsp;</td>
                    <td style="padding: 5px;vertical-align: top;text-align: right;border-bottom: 3px double #000;border-top: 3px solid #000;">{{ number_format($headingTotal) }}</td>
                </tr>
                <tr class="heading">
                    <td colspan="3" style="padding: 5px; vertical-align: top; border-bottom: 1px solid #efefef;">&nbsp;</td>
                </tr>
            @endforeach
        @endif

    </table>
</div>
</body>
</html>
