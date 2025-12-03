<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string $goal_name
 * @property int $goal_completions
 * @property numeric $goal_value
 * @property numeric $conversion_rate
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\AnalyticsConversionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereConversionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereGoalCompletions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereGoalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereGoalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsConversion whereUpdatedAt($value)
 */
	final class AnalyticsConversion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string $event_name
 * @property string $event_category
 * @property string $event_action
 * @property string|null $event_label
 * @property int $event_count
 * @property numeric|null $event_value
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\AnalyticsEventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereEventValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsEvent whereUpdatedAt($value)
 */
	final class AnalyticsEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string|null $page_path
 * @property string|null $page_title
 * @property int $pageviews
 * @property int $unique_pageviews
 * @property int $avg_time_on_page
 * @property int $entrances
 * @property numeric $bounce_rate
 * @property numeric $exit_rate
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\AnalyticsPageviewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereAvgTimeOnPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereBounceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereEntrances($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereExitRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview wherePagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview wherePageviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereUniquePageviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsPageview whereUpdatedAt($value)
 */
	final class AnalyticsPageview extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property int $sessions
 * @property int $users
 * @property int $new_users
 * @property numeric $bounce_rate
 * @property int $avg_session_duration
 * @property numeric $pages_per_session
 * @property string|null $source
 * @property string|null $medium
 * @property string|null $campaign
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\AnalyticsSessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereAvgSessionDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereBounceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereNewUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession wherePagesPerSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereSessions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSession whereUsers($value)
 */
	final class AnalyticsSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property array<array-key, mixed>|null $dimensions
 * @property array<array-key, mixed>|null $metrics
 * @property \App\Enums\GoogleAnalitycs\OrderByType|null $order_by_type
 * @property array<array-key, mixed>|null $order_by
 * @property string|null $order_by_direction
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Database\Factories\AnalyticsSettingsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereDimensions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereMetrics($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereOrderByDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereOrderByType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnalyticsSettings whereUpdatedAt($value)
 */
	final class AnalyticsSettings extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $google_service_account
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Database\Factories\GlobalSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting whereGoogleServiceAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GlobalSetting whereUpdatedAt($value)
 */
	final class GlobalSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property \App\Enums\KpiDataSource $data_source
 * @property string $source_type
 * @property \App\Enums\KpiCategory $category
 * @property string|null $formula
 * @property string $format
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property numeric|null $target_value
 * @property \Carbon\CarbonImmutable|null $target_date
 * @property \Carbon\CarbonImmutable|null $from_date
 * @property \Carbon\CarbonImmutable|null $comparison_start_date
 * @property \Carbon\CarbonImmutable|null $comparison_end_date
 * @property \App\Enums\KpiGoalType|null $goal_type
 * @property string|null $page_path
 * @property \App\Enums\KpiValueType|null $value_type
 * @property string|null $metric_type
 * @property bool $is_active
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\KpiFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereComparisonEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereComparisonStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereDataSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereGoalType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereMetricType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi wherePagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereTargetDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kpi whereValueType($value)
 */
	final class Kpi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string $page_url
 * @property string|null $country
 * @property string $device
 * @property int $impressions
 * @property int $clicks
 * @property numeric $ctr
 * @property numeric $position
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\SearchPageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereCtr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage wherePageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchPage whereUpdatedAt($value)
 */
	final class SearchPage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Carbon\CarbonImmutable $date
 * @property string $query
 * @property string|null $country
 * @property string $device
 * @property int $impressions
 * @property int $clicks
 * @property numeric $ctr
 * @property numeric $position
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\SearchQueryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereCtr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereQuery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchQuery whereUpdatedAt($value)
 */
	final class SearchQuery extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Database\Factories\SearchSitemapFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchSitemap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchSitemap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SearchSitemap query()
 */
	final class SearchSitemap extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $property_id
 * @property string $google_tag_id
 * @property string $site_url
 * @property \Carbon\CarbonImmutable|null $last_sync_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Database\Factories\SettingsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereGoogleTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereLastSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereSiteUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Settings whereUpdatedAt($value)
 */
	final class Settings extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnalyticsConversion> $analyticsConversions
 * @property-read int|null $analytics_conversions_count
 * @property-read bool|null $analytics_conversions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnalyticsEvent> $analyticsEvents
 * @property-read int|null $analytics_events_count
 * @property-read bool|null $analytics_events_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnalyticsPageview> $analyticsPageviews
 * @property-read int|null $analytics_pageviews_count
 * @property-read bool|null $analytics_pageviews_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnalyticsSession> $analyticsSessions
 * @property-read int|null $analytics_sessions_count
 * @property-read bool|null $analytics_sessions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kpi> $kpis
 * @property-read int|null $kpis_count
 * @property-read bool|null $kpis_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SearchPage> $searchPages
 * @property-read int|null $search_pages_count
 * @property-read bool|null $search_pages_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SearchQuery> $searchQueries
 * @property-read int|null $search_queries_count
 * @property-read bool|null $search_queries_exists
 * @property-read \App\Models\Settings|null $settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read bool|null $users_exists
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 */
	final class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read bool|null $notifications_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read bool|null $permissions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read bool|null $roles_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read bool|null $teams_exists
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	final class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser, \Filament\Models\Contracts\HasTenants {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with device breakdown data
 *
 * @property int $id
 * @property string|null $device
 * @property int|null $impressions
 * @property int|null $clicks
 * @property float|null $ctr
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel whereCtr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceBreakdownModel whereImpressions($value)
 */
	final class DeviceBreakdownModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with Google Analytics session sources data
 *
 * @property int $id
 * @property string|null $source
 * @property string|null $medium
 * @property int|null $sessions
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel whereMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel whereSessions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionSourceModel whereSource($value)
 */
	final class SessionSourceModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with Google Analytics data
 *
 * @property int $id
 * @property string|null $source
 * @property string|null $medium
 * @property string|null $page_path
 * @property string|null $page_title
 * @property int|null $sessions
 * @property int|null $users
 * @property int|null $pageviews
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel whereMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel wherePagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel wherePageviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel whereSessions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SourcePageModel whereUsers($value)
 */
	final class SourcePageModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with Google Analytics top pages data
 *
 * @property int $id
 * @property string|null $page_title
 * @property string|null $page_path
 * @property int|null $views
 * @property int|null $active_users
 * @property int|null $event_count
 * @property int|null $bounce_rate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel whereActiveUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel whereBounceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel whereEventCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel wherePagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopPageModel whereViews($value)
 */
	final class TopPageModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with aggregated Search Page data
 *
 * @property int $id
 * @property string|null $page_url
 * @property int|null $impressions
 * @property int|null $clicks
 * @property float|null $ctr
 * @property float|null $position
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel whereCtr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel whereImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel wherePageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchPageModel wherePosition($value)
 */
	final class TopSearchPageModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with aggregated Search Query data
 *
 * @property int $id
 * @property string|null $query
 * @property int|null $impressions
 * @property int|null $clicks
 * @property float|null $ctr
 * @property float|null $position
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel whereClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel whereCtr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel whereImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TopSearchQueryModel whereQuery($value)
 */
	final class TopSearchQueryModel extends \Eloquent {}
}

namespace App\Support{
/**
 * Temporary model for Sushi to work with Google Analytics user sources data
 *
 * @property int $id
 * @property string|null $source
 * @property string|null $medium
 * @property int|null $users
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel whereMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSourceModel whereUsers($value)
 */
	final class UserSourceModel extends \Eloquent {}
}

