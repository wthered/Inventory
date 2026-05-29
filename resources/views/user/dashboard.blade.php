@extends('templates.general')

@section('title', 'Dashboard')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}"/>
@endsection

@section('content')
	<div class="dashboard-container">
		<h1 class="page-title">{{ __('messages.dashboard_overview') }}</h1>

		<div class="dashboard-grid">
			<!-- 1. SUMMARY DECK -->
			<section class="summary-deck">
				{{-- Συνολικά Προϊόντα --}}
				<div class="card metric-card">
					<div class="metric-card-header">
						<h3>{{ __('messages.summary.total_products') }}</h3>
						<i class="fas fa-boxes"></i>
					</div>
					<p>{{ number_format($products) }}</p>
					<span class="caption">{{ $newItemsCount }} {{ __('messages.summary.new_items_month') }}</span>
				</div>

				{{-- Συνολική Αξία --}}
				<div class="card metric-card">
					<div class="metric-card-header">
						<h3>{{ __('messages.summary.total_value') }}</h3>
						<i class="fas fa-euro-sign"></i>
					</div>
					<p>€2.4M</p>
					<span class="caption">+1.5% {{ __('messages.summary.from_last_month') }}</span>
				</div>

				{{-- Χαμηλό Απόθεμα --}}
				<div class="card metric-card">
					<div class="metric-card-header">
						<h3>{{ __('messages.summary.low_stock') }}</h3>
						<i class="fas fa-layer-group"></i>
					</div>
					<p>38</p>
					<span class="caption">{{ __('messages.summary.low_stock_caption') }}</span>
				</div>

				{{-- Έλλειψη Αποθέματος --}}
				<div class="card metric-card">
					<div class="metric-card-header">
						<h3>{{ __('messages.summary.out_of_stock') }}</h3>
						<i class="fas fa-exclamation-circle"></i>
					</div>
					<p>5</p>
					<span class="caption">{{ __('messages.summary.out_of_stock_caption') }}</span>
				</div>
			</section>

			<!-- 2. MAIN REPORTS PANEL (Charts) -->
			<section class="card main-reports-panel">
				<div class="section-header">
					<h2>{{ __('messages.reports.inventory_value_trend') }}</h2>
					<a href="#">{{ __('messages.reports.view_report') }}</a>
				</div>
				<!-- Placeholder for a chart library like Chart.js or D3 -->
				<div class="chart-placeholder">
					{{ __('messages.reports.chart_placeholder') }}
					<i class="fas fa-chart-line"></i>
				</div>
			</section>

			<!-- 3. ACTIVITY FEED -->
			<section class="card activity-feed-panel">
				<div class="section-header">
					<h2>{{ __('messages.recent_activity') }}</h2>
					<a href="#">{{ __('messages.view_all') }}</a>
				</div>
				<ul class="activity-list">
					{{-- Παραλαβή Προϊόντος --}}
					<li>
						<div class="activity-icon"><i class="fas fa-file-download"></i></div>
						<div class="activity-details">
							<p>
								{!! __('messages.activity_received', [
								   'units' => '<strong style="color: var(--color-accent);">150 units</strong>',
								   'product' => 'Product X'
								]) !!}
							</p>
						</div>
						<span class="activity-time">2 {{ __('messages.minutes_ago') }}</span>
					</li>

					{{-- Αποστολή Προϊόντος --}}
					<li>
						<div class="activity-icon"><i class="fas fa-file-upload"></i></div>
						<div class="activity-details">
							<p>
								{!! __('messages.activity_shipped', [
								   'units' => '<strong style="color: var(--color-error);">12 units</strong>',
								   'target' => 'Retailer A'
								]) !!}
							</p>
						</div>
						<span class="activity-time">1 {{ __('messages.hour_ago') }}</span>
					</li>

					{{-- Ενημέρωση Τιμής --}}
					<li>
						<div class="activity-icon"><i class="fas fa-pen"></i></div>
						<div class="activity-details">
							<p>
								{!! __('messages.activity_price_update', [
								   'product' => '<strong>Gadget Pro 3000</strong>'
								]) !!}
							</p>
						</div>
						<span class="activity-time">3 {{ __('messages.hours_ago') }}</span>
					</li>

					{{-- Νέος Προμηθευτής (Σωστό: χωρίς το "1") --}}
					<li>
						<div class="activity-icon"><i class="fas fa-user-plus"></i></div>
						<div class="activity-details">
							<p>
								{!! __('messages.activity_supplier_added', [
								   'supplier' => '<strong>TechCorp</strong>'
								]) !!}
							</p>
						</div>
						<span class="activity-time">{{ __('messages.day_ago') }}</span>
					</li>

					{{-- Stock Alert (Σωστό: Περνάμε το 2 μέσα στο array) --}}
					<li>
						<div class="activity-icon"><i class="fas fa-exclamation-triangle"></i></div>
						<div class="activity-details">
							<p>
								{!! __('messages.activity_stock_alert', [
								   'product' => '<strong>Widget B</strong>'
								]) !!}
							</p>
						</div>
						<span class="activity-time">{{ __('messages.days_ago', ['days' => 2]) }}</span>
					</li>
				</ul>
			</section>
		</div>
	</div>
@endsection