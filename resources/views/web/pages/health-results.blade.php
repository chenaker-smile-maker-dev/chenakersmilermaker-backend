@extends('web.layouts.main')

@section('title', __('System Health | :app', ['app' => config('app.name')]))

@section('html_class', $theme === 'dark' ? 'dark' : '')

@section('body_class', 'bg-[#FDFDFC] dark:bg-[#050505] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex flex-col items-center pt-10 pb-16 px-6 lg:px-12')

@push('head')
	{!! $assets !!}
@endpush

@section('header')
	<div class="flex items-center gap-3">
		<a
			href="{{ request()->fullUrlWithQuery(['fresh' => 1]) }}"
			class="inline-flex items-center gap-2 px-4 py-1.5 rounded-sm text-sm font-medium border border-[#19140035] dark:border-[#3E3E3A] hover:border-black dark:hover:border-white transition"
		>
			<span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
			{{ __('Run health checks') }}
		</a>
		<a
			href="{{ request()->url() }}"
			class="inline-flex items-center px-4 py-1.5 rounded-sm text-sm font-medium border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] transition"
		>
			{{ __('Reset filters') }}
		</a>
	</div>
@endsection

@section('main_wrapper_class', 'w-full max-w-7xl mx-auto flex flex-col gap-8')

@section('content')
	@php
		$results = collect($checkResults?->storedCheckResults ?? []);
		$statusCounts = [
			'ok' => $results->where('status', 'ok')->count(),
			'warning' => $results->where('status', 'warning')->count(),
			'failed' => $results->where('status', 'failed')->count(),
		];
		$overallStatus = $results->contains(fn ($result) => in_array($result->status, ['failed', 'crashed'], true))
			? 'failed'
			: ($results->contains(fn ($result) => $result->status === 'warning') ? 'warning' : 'ok');
		$statusConfig = [
			'ok' => ['label' => __('Operational'), 'badge' => 'bg-emerald-500/15 text-emerald-500 border border-emerald-500/30'],
			'warning' => ['label' => __('Attention needed'), 'badge' => 'bg-amber-500/15 text-amber-500 border border-amber-500/30'],
			'failed' => ['label' => __('Outage detected'), 'badge' => 'bg-rose-500/15 text-rose-500 border border-rose-500/30'],
		];
		$supportEmail = config('mail.from.address') ?? 'support@example.com';
	@endphp

	<section class="grid gap-6 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1fr)]">
		<div class="relative overflow-hidden rounded-2xl border border-[#19140015] dark:border-white/10 bg-white dark:bg-[#0f0f0f] p-6 lg:p-8 shadow-[0px_12px_40px_rgba(12,12,12,0.05)] dark:shadow-[0px_12px_40px_rgba(0,0,0,0.45)]">
			<div class="absolute -top-10 -right-10 h-36 w-36 rounded-full bg-gradient-to-br from-sky-400/40 via-transparent to-transparent blur-2xl"></div>
			<div class="relative flex flex-col gap-6">
				<div class="flex flex-wrap items-center justify-between gap-3">
					<div>
						<p class="text-xs uppercase tracking-[0.2em] text-[#706f6c] dark:text-[#8e8d88]">{{ __('Live system status') }}</p>
						<h1 class="mt-2 text-2xl lg:text-3xl font-semibold">
							{{ __(':app health center', ['app' => config('app.name')]) }}
						</h1>
					</div>
					<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $statusConfig[$overallStatus]['badge'] }}">
						<span class="h-2 w-2 rounded-full {{ $overallStatus === 'ok' ? 'bg-emerald-500' : ($overallStatus === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
						{{ $statusConfig[$overallStatus]['label'] }}
					</span>
				</div>

				<dl class="grid grid-cols-3 gap-3 text-sm">
					<div class="rounded-lg border border-[#19140012] dark:border-white/10 bg-[#faf9f7] dark:bg-white/5 px-4 py-3">
						<dt class="text-[#706f6c] dark:text-[#9f9e97]">{{ __('Passing') }}</dt>
						<dd class="mt-1 text-xl font-semibold">{{ $statusCounts['ok'] }}</dd>
					</div>
					<div class="rounded-lg border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-amber-700 dark:text-amber-200">
						<dt>{{ __('Warnings') }}</dt>
						<dd class="mt-1 text-xl font-semibold">{{ $statusCounts['warning'] }}</dd>
					</div>
					<div class="rounded-lg border border-rose-500/25 bg-rose-500/10 px-4 py-3 text-rose-700 dark:text-rose-200">
						<dt>{{ __('Failed') }}</dt>
						<dd class="mt-1 text-xl font-semibold">{{ $statusCounts['failed'] }}</dd>
					</div>
				</dl>

				<div class="flex flex-wrap items-center justify-between gap-3 text-sm text-[#706f6c] dark:text-[#9f9e97]">
					<div class="flex items-center gap-2">
						<span class="h-2 w-2 rounded-full bg-gradient-to-r from-emerald-400 to-sky-400"></span>
						@if ($lastRanAt)
							<span>
								{{ __('Last check ran :time', ['time' => $lastRanAt->diffForHumans()]) }}
							</span>
						@else
							<span>{{ __('No health checks have been executed yet.') }}</span>
						@endif
					</div>
					<div class="flex items-center gap-2">
						<span class="h-2 w-2 rounded-full bg-gradient-to-r from-purple-400 to-indigo-400"></span>
						<span>{{ __('Total checks: :count', ['count' => $results->count()]) }}</span>
					</div>
				</div>
			</div>
		</div>

		<div class="grid content-start gap-4">
			<div class="rounded-2xl border border-transparent bg-gradient-to-br from-emerald-500/15 via-sky-500/5 to-white dark:from-emerald-500/15 dark:via-sky-500/10 dark:to-[#101010] p-[1px]">
				<div class="rounded-2xl bg-white dark:bg-[#080808] p-6 shadow-[0px_16px_35px_rgba(0,0,0,0.08)] dark:shadow-[0px_16px_45px_rgba(0,0,0,0.5)]">
					<h2 class="text-lg font-semibold">{{ __('Health insights') }}</h2>
					<p class="mt-2 text-sm text-[#706f6c] dark:text-[#9f9e97]">
						{{ __('Review each subsystem to understand current status, performance notes, and any remediation steps needed.') }}
					</p>
				</div>
			</div>

			<div class="grid gap-4 sm:grid-cols-2">
				<article class="rounded-xl border border-[#19140012] dark:border-white/10 bg-white dark:bg-[#0b0b0b] p-5 shadow-sm">
					<h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#706f6c] dark:text-[#9f9e97]">{{ __('Quick tips') }}</h3>
					<ul class="mt-4 space-y-3 text-sm text-[#40403c] dark:text-[#d7d6cf]">
						<li class="flex items-start gap-3">
							<span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
							<span>{{ __('Keep schedules frequent enough to catch regressions early.') }}</span>
						</li>
						<li class="flex items-start gap-3">
							<span class="mt-1 h-1.5 w-1.5 rounded-full bg-sky-400"></span>
							<span>{{ __('Subscribe to notifications so your team learns about issues instantly.') }}</span>
						</li>
						<li class="flex items-start gap-3">
							<span class="mt-1 h-1.5 w-1.5 rounded-full bg-amber-400"></span>
							<span>{{ __('Investigate warning signals before they escalate into failures.') }}</span>
						</li>
					</ul>
				</article>

				<article class="rounded-xl border border-[#19140012] dark:border-white/10 bg-white dark:bg-[#0b0b0b] p-5 shadow-sm">
					<h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#706f6c] dark:text-[#9f9e97]">{{ __('Need support?') }}</h3>
					<p class="mt-3 text-sm text-[#40403c] dark:text-[#d7d6cf]">
						{{ __('Share these results with your DevOps channel and link the failing checks. Together we can keep :app resilient.', ['app' => config('app.name')]) }}
					</p>
					<a
						href="mailto:{{ $supportEmail }}"
						class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:underline"
					>
						{{ __('Contact operations') }}
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
							<path fill-rule="evenodd" d="M10.75 4.5a.75.75 0 0 1 .75-.75h4a.75.75 0 0 1 .75.75v4a.75.75 0 0 1-1.5 0V6.31l-6.22 6.22a.75.75 0 1 1-1.06-1.06l6.22-6.22h-2.19a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
							<path d="M5.75 5a.75.75 0 0 1 .75.75v8.5h8.5a.75.75 0 0 1 0 1.5h-9.25a.75.75 0 0 1-.75-.75V5.75A.75.75 0 0 1 5.75 5Z" />
						</svg>
					</a>
				</article>
			</div>
		</div>
	</section>

	@if ($results->isNotEmpty())
		<section class="mt-10 space-y-4">
			<h2 class="text-xl font-semibold">{{ __('Check details') }}</h2>
			<p class="text-sm text-[#706f6c] dark:text-[#9f9e97]">
				{{ __('Dive into each health check for granular diagnostics, runtime notes, and the latest summary.') }}
			</p>

			<dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
				@foreach ($results as $result)
					@php
						$stateKey = $result->status === 'ok' ? 'ok' : ($result->status === 'warning' ? 'warning' : 'failed');
						$state = $statusConfig[$stateKey];
						$shortSummary = data_get($result, 'meta.shortSummary') ?? $result->shortSummary;
						$message = $result->notificationMessage ?? data_get($result, 'meta.message');
						$endedAt = data_get($result, 'endedAt');
					@endphp
					<div class="group relative overflow-hidden rounded-2xl border border-[#19140012] dark:border-white/10 bg-white dark:bg-[#090909] p-5 transition-shadow hover:shadow-lg hover:-translate-y-0.5">
						<div class="absolute inset-x-0 top-0 h-0.5 {{ $stateKey === 'ok' ? 'bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-400' : ($stateKey === 'warning' ? 'bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400' : 'bg-gradient-to-r from-rose-500 via-rose-600 to-rose-500') }}"></div>
						<div class="flex items-start gap-3">
							<span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f5f4f1] dark:bg-white/5">
								<x-health-status-indicator :result="$result" />
							</span>
							<div class="min-w-0">
								<dd class="text-base font-semibold leading-tight text-[#1b1b18] dark:text-white">
									{{ $result->label }}
								</dd>
								<dt class="mt-1 text-sm text-[#706f6c] dark:text-[#9f9e97]">
									{{ $shortSummary }}
								</dt>
							</div>
						</div>
						<div class="mt-4 flex items-center justify-between text-xs text-[#706f6c] dark:text-[#9f9e97]">
							<span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $state['badge'] }} uppercase tracking-wide">
								{{ $result->status }}
							</span>
							@if ($endedAt)
								<span>{{ optional($endedAt)->diffForHumans() }}</span>
							@endif
						</div>
						@if ($message)
							<div class="mt-4 rounded-lg border border-dashed border-[#19140012] dark:border-white/10 bg-[#faf9f7] dark:bg-white/5 p-3 text-sm text-[#40403c] dark:text-[#d7d6cf]">
								{{ $message }}
							</div>
						@endif
					</div>
				@endforeach
			</dl>
		</section>
	@else
		<section class="mt-10">
			<div class="rounded-2xl border border-dashed border-[#19140035] dark:border-white/10 bg-white/70 dark:bg-[#0b0b0b]/60 p-8 text-center">
				<h2 class="text-lg font-semibold">{{ __('No health results yet') }}</h2>
				<p class="mt-2 text-sm text-[#706f6c] dark:text-[#9f9e97]">
					{{ __('Run your first health check to populate this dashboard with live insights.') }}
				</p>
				<a
					href="{{ request()->fullUrlWithQuery(['fresh' => 1]) }}"
					class="mt-4 inline-flex items-center gap-2 rounded-md bg-[#1b1b18] px-5 py-2 text-sm font-medium text-white hover:bg-black dark:bg-white dark:text-[#0a0a0a] dark:hover:bg-[#f7f7f7]"
				>
					{{ __('Run checks now') }}
				</a>
			</div>
		</section>
	@endif
@endsection
