<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the published company named by the {companySlug} route segment and the
 * guest's language, so controllers never look either up themselves.
 *
 * The language is ?lang= on pages and "locale" in JSON bodies; an unsupported
 * value falls back to the company's default language.
 */
class ResolveCompany
{
    private const COMPANY = 'company';

    private const LOCALE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $company = Company::query()
            ->published()
            ->where('slug', $request->route('companySlug'))
            ->firstOrFail();

        $requested = $request->query('lang', $request->input('locale'));

        $locale = in_array($requested, config('company.locales'), true) ? $requested : $company->default_locale;

        $request->attributes->set(self::COMPANY, $company);
        $request->attributes->set(self::LOCALE, $locale);

        app()->setLocale($locale);

        return $next($request);
    }

    public static function company(Request $request): Company
    {
        return $request->attributes->get(self::COMPANY);
    }

    public static function locale(Request $request): string
    {
        return $request->attributes->get(self::LOCALE);
    }
}
