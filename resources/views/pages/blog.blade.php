@extends('layouts.app')

@section('title', 'Blog | CleanTech')

@push('styles')
@include('pages._theme')
@endpush

@section('content')
<div class="ct-page">
    <section class="ct-hero">
        <div class="container">
            <div class="ct-hero-shell">
                <div class="ct-hero-main ct-reveal">
                    <span class="ct-eyebrow">CleanTech Blog</span>
                    <h1 class="ct-title">Short reads for cleaner homes and better booking choices.</h1>
                    <p class="ct-lead">Helpful tips, light reading, and quick reminders that fit the actual services on CleanTech.</p>
                </div>

                <div class="ct-photo-card tall ct-reveal delay-1">
                    <img src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=80" alt="Workspace and planning">
                </div>
            </div>
        </div>
    </section>

    <section class="ct-section">
        <div class="container">
            <div class="ct-grid three">
                <article class="ct-blog-card ct-reveal">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=1400&q=80" alt="Cleaning tools">
                    </div>
                    <div class="ct-blog-meta">Cleaning tips</div>
                    <h3>Easy habits that keep mess from building up</h3>
                    <p>Small routines that make weekly cleaning feel lighter.</p>
                </article>

                <article class="ct-blog-card ct-reveal delay-1">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80" alt="Home cleaning scene">
                    </div>
                    <div class="ct-blog-meta">Home care</div>
                    <h3>When a deep clean makes more sense</h3>
                    <p>Know when routine cleaning is no longer enough.</p>
                </article>

                <article class="ct-blog-card ct-reveal delay-2">
                    <div class="ct-card-media">
                        <img src="https://images.unsplash.com/photo-1497366412874-3415097a27e7?auto=format&fit=crop&w=1400&q=80" alt="Clean office space">
                    </div>
                    <div class="ct-blog-meta">Workspaces</div>
                    <h3>Why cleaner spaces improve focus</h3>
                    <p>A neat room or office changes how people work and feel.</p>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
