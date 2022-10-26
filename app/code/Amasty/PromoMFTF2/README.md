# Auto Add Promo Items MFTF2

### 32 Free Gift specific tests, grouped by purpose, for greater convenience.

        Tests group: Promo
        Runs all tests.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group Promo -r

        Tests group: PromoConfiguration
        Runs tests related to extension configuration.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group PromoConfiguration -r

        Tests group: PromoFreeGifts
        Runs tests related to extension creation rules and adding free gifts.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group PromoFreeGifts -r

        Tests group: PromoBannersLite
        Runs tests related to extension the display of banners on product pages.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group PromoBannersLite -r
