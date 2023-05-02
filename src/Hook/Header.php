<?php

namespace Oksydan\Module\IsThemeCore\Hook;

use Oksydan\Module\IsThemeCore\Core\Breadcrumbs\ThemeBreadcrumbs;
use Oksydan\Module\IsThemeCore\Core\ListingDisplay\ThemeListDisplay;
use Oksydan\Module\IsThemeCore\Core\StructuredData\BreadcrumbStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\ProductStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\ShopStructuredData;
use Oksydan\Module\IsThemeCore\Core\StructuredData\StructuredDataInterface;
use Oksydan\Module\IsThemeCore\Core\StructuredData\WebsiteStructuredData;
use Oksydan\Module\IsThemeCore\Form\Settings\GeneralConfiguration;
use Oksydan\Module\IsThemeCore\Form\Settings\WebpConfiguration;

class Header extends AbstractHook
{
    public const HOOK_LIST = [
        'displayHeader',
    ];

    public function hookDisplayHeader(): string
    {
        $themeListDisplay = new ThemeListDisplay();
        $breadcrumbs = (new ThemeBreadcrumbs())->getBreadcrumb();

        if ($breadcrumbs['count']) {
            $this->context->smarty->assign([
                'breadcrumb' => $breadcrumbs,
            ]);
        }

        $this->context->smarty->assign([
            'listingDisplayType' => $themeListDisplay->getDisplay(),
            'preloadCss' => \Configuration::get(GeneralConfiguration::THEMECORE_PRELOAD_CSS),
            'webpEnabled' => \Configuration::get(WebpConfiguration::THEMECORE_WEBP_ENABLED),
            'jsonData' => $this->getStructuredData(),
        ]);

        return $this->module->fetch('module:is_themecore/views/templates/hook/head.tpl');
    }

    public function getStructuredData(): array
    {
        $dataArray = [];

        if ($this->context->controller instanceof \ProductControllerCore && $this->context->controller->getProduct()->id !== null) {
            try {
                $productData = $this->module->get(ProductStructuredData::class);
            } catch (\Exception $e) {
                $productData = null;
            }

            if ($productData instanceof StructuredDataInterface) {
                $dataArray[] = $productData->getFormattedData();
            }
        }

        try {
            $breadcrumbData = $this->module->get(BreadcrumbStructuredData::class);
        } catch (\Exception $e) {
            $breadcrumbData = null;
        }

        if ($breadcrumbData instanceof StructuredDataInterface) {
            $dataArray[] = $breadcrumbData->getFormattedData();
        }

        try {
            $shopData = $this->module->get(ShopStructuredData::class);
        } catch (\Exception $e) {
            $shopData = null;
        }

        if ($shopData instanceof StructuredDataInterface) {
            $dataArray[] = $shopData->getFormattedData();
        }

        if ($this->context->controller->getPageName() === 'index') {
            try {
                $website = $this->module->get(WebsiteStructuredData::class);
            } catch (\Exception $e) {
                $website = null;
            }

            if ($website instanceof StructuredDataInterface) {
                $dataArray[] = $website->getFormattedData();
            }
        }

        return $dataArray;
    }
}
