<style>
body{
    background:
        radial-gradient(circle at top left, rgba(56,189,248,.12), transparent 28%),
        radial-gradient(circle at top right, rgba(14,165,233,.10), transparent 24%),
        linear-gradient(180deg, #030712 0%, #071120 55%, #020617 100%);
    color:#e5eefc;
}

.navbar{
    background:rgba(3,7,18,.86) !important;
    backdrop-filter:blur(16px);
    border-bottom:1px solid rgba(255,255,255,.08);
}

.ct-page{
    position:relative;
    overflow:hidden;
}

.ct-page::before{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    background:
        linear-gradient(140deg, rgba(255,255,255,.03), transparent 34%),
        linear-gradient(320deg, rgba(56,189,248,.05), transparent 38%);
}

.ct-hero{
    position:relative;
    padding:108px 0 36px;
}

.ct-hero-shell{
    position:relative;
    z-index:1;
    display:grid;
    grid-template-columns:minmax(0, 1.2fr) minmax(280px, .8fr);
    gap:1.2rem;
    align-items:stretch;
}

.ct-hero-main,
.ct-hero-side,
.ct-panel,
.ct-card,
.ct-step,
.ct-faq-item,
.ct-price-card,
.ct-blog-card{
    background:linear-gradient(180deg, rgba(9,18,36,.95), rgba(4,11,24,.98));
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px;
    box-shadow:0 24px 60px rgba(0,0,0,.28);
}

.ct-hero-main{
    padding:2.35rem;
}

.ct-hero-side{
    padding:1.6rem;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    gap:1rem;
}

.ct-eyebrow{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    padding:.45rem .78rem;
    border-radius:999px;
    border:1px solid rgba(56,189,248,.25);
    background:rgba(56,189,248,.10);
    color:#c7ebff;
    font-size:.78rem;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.ct-title{
    margin:1rem 0 .85rem;
    font-size:clamp(2rem, 4.4vw, 3.4rem);
    line-height:.98;
    font-weight:950;
    letter-spacing:-.04em;
    color:#fff;
}

.ct-lead{
    margin:0;
    max-width:760px;
    color:#b9c8e2;
    font-size:1rem;
    line-height:1.7;
}

.ct-badges{
    display:flex;
    flex-wrap:wrap;
    gap:.7rem;
    margin-top:1.25rem;
}

.ct-badge{
    display:inline-flex;
    align-items:center;
    min-height:38px;
    padding:.48rem .82rem;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:#dce8fb;
    font-size:.85rem;
    font-weight:800;
}

.ct-side-label{
    color:#8ea2c5;
    font-size:.72rem;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
}

.ct-side-value{
    margin-top:.35rem;
    color:#fff;
    font-size:1.2rem;
    font-weight:900;
}

.ct-side-copy{
    color:#b7c5de;
    font-size:.94rem;
    line-height:1.6;
}

.ct-actions{
    display:flex;
    flex-wrap:wrap;
    gap:.8rem;
    margin-top:1.4rem;
}

.ct-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:46px;
    padding:.78rem 1.15rem;
    border-radius:14px;
    border:1px solid rgba(56,189,248,.28);
    background:linear-gradient(135deg, rgba(14,165,233,.22), rgba(37,99,235,.18));
    color:#fff;
    text-decoration:none;
    font-weight:900;
    transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.ct-button:hover{
    transform:translateY(-2px);
    box-shadow:0 16px 30px rgba(14,165,233,.16);
    color:#fff;
}

.ct-button.secondary{
    background:rgba(255,255,255,.03);
    border-color:rgba(255,255,255,.10);
    color:#dce8fb;
}

.ct-section{
    position:relative;
    z-index:1;
    padding:0 0 34px;
}

.ct-section-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:1rem;
    flex-wrap:wrap;
    margin-bottom:1rem;
}

.ct-section-title{
    margin:0;
    font-size:1.55rem;
    font-weight:950;
    color:#fff;
    letter-spacing:-.03em;
}

.ct-section-copy{
    margin:.3rem 0 0;
    color:#9eb0ca;
    max-width:660px;
}

.ct-grid{
    display:grid;
    gap:1rem;
}

.ct-grid.two{
    grid-template-columns:repeat(2, minmax(0, 1fr));
}

.ct-grid.three{
    grid-template-columns:repeat(3, minmax(0, 1fr));
}

.ct-grid.four{
    grid-template-columns:repeat(4, minmax(0, 1fr));
}

.ct-card{
    padding:1.35rem;
}

.ct-card h3,
.ct-card h4,
.ct-blog-card h3,
.ct-price-card h3,
.ct-step h3{
    margin:0 0 .55rem;
    color:#fff;
    font-size:1.1rem;
    font-weight:900;
}

.ct-card p,
.ct-blog-card p,
.ct-step p,
.ct-faq-item p,
.ct-price-copy,
.ct-muted{
    color:#9eb0ca;
    line-height:1.65;
    margin:0;
}

.ct-card .ct-kicker,
.ct-blog-meta,
.ct-price-badge{
    color:#8fd8ff;
    font-size:.78rem;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:.6rem;
}

.ct-list{
    display:grid;
    gap:.7rem;
    margin-top:.9rem;
}

.ct-list-row{
    display:grid;
    grid-template-columns:auto 1fr;
    gap:.75rem;
    align-items:flex-start;
}

.ct-list-mark{
    width:26px;
    height:26px;
    border-radius:999px;
    display:grid;
    place-items:center;
    background:rgba(56,189,248,.12);
    border:1px solid rgba(56,189,248,.24);
    color:#d7f1ff;
    font-size:.82rem;
    font-weight:900;
}

.ct-metric-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:.9rem;
}

.ct-metric{
    padding:1rem 1.05rem;
    border-radius:18px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.08);
}

.ct-metric-label{
    color:#89a0c4;
    font-size:.72rem;
    font-weight:900;
    letter-spacing:.12em;
    text-transform:uppercase;
}

.ct-metric-value{
    margin-top:.45rem;
    color:#fff;
    font-size:1.5rem;
    font-weight:950;
}

.ct-step-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:1rem;
}

.ct-step{
    padding:1.3rem;
}

.ct-step-number{
    width:42px;
    height:42px;
    border-radius:14px;
    display:grid;
    place-items:center;
    background:rgba(56,189,248,.12);
    border:1px solid rgba(56,189,248,.26);
    color:#d7f1ff;
    font-weight:950;
    margin-bottom:.95rem;
}

.ct-blog-card{
    padding:1.35rem;
    height:100%;
}

.ct-blog-card.featured{
    grid-column:span 2;
}

.ct-price-card{
    padding:1.5rem;
    height:100%;
}

.ct-price{
    margin:.7rem 0 .85rem;
    color:#fff;
    font-size:2rem;
    font-weight:950;
}

.ct-price-sub{
    color:#8ea2c5;
    font-size:.88rem;
}

.ct-price-list{
    display:grid;
    gap:.55rem;
    margin-top:1rem;
}

.ct-price-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    color:#dfe9fb;
    padding:.65rem .85rem;
    border-radius:14px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.06);
}

.ct-price-item small{
    color:#8ea2c5;
    font-size:.82rem;
}

.ct-faq-stack{
    display:grid;
    gap:.8rem;
}

.ct-faq-item{
    padding:1.15rem 1.2rem;
}

.ct-faq-item summary{
    list-style:none;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    color:#fff;
    font-weight:900;
}

.ct-faq-item summary::-webkit-details-marker{
    display:none;
}

.ct-faq-item summary::after{
    content:"+";
    width:28px;
    height:28px;
    border-radius:999px;
    display:grid;
    place-items:center;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.08);
    color:#8fd8ff;
    flex-shrink:0;
}

.ct-faq-item[open] summary::after{
    content:"–";
}

.ct-faq-item p{
    margin-top:.9rem;
}

.ct-contact-grid{
    display:grid;
    grid-template-columns:minmax(0, .9fr) minmax(0, 1.1fr);
    gap:1rem;
}

.ct-contact-card{
    padding:1.4rem;
}

.ct-contact-list{
    display:grid;
    gap:.75rem;
    margin-top:1rem;
}

.ct-contact-line{
    padding:.82rem .95rem;
    border-radius:16px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.08);
}

.ct-contact-line strong{
    display:block;
    color:#fff;
    margin-bottom:.25rem;
}

.ct-inline-link{
    color:#8fd8ff;
    text-decoration:none;
    font-weight:800;
}

.ct-inline-link:hover{
    color:#fff;
}

.ct-form-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:.9rem;
    margin-top:1rem;
}

.ct-form-grid .full{
    grid-column:1 / -1;
}

.ct-input,
.ct-select,
.ct-textarea{
    width:100%;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(3,10,20,.9);
    color:#f8fbff;
    padding:.88rem .95rem;
    box-shadow:none;
}

.ct-input::placeholder,
.ct-textarea::placeholder{
    color:#7f93b5;
}

.ct-input:focus,
.ct-select:focus,
.ct-textarea:focus{
    outline:none;
    border-color:rgba(56,189,248,.34);
    box-shadow:0 0 0 3px rgba(56,189,248,.12);
}

.ct-select option{
    background:#07111f;
    color:#f8fafc;
}

.ct-note{
    margin-top:.9rem;
    color:#8ea2c5;
    font-size:.9rem;
}

.ct-divider{
    height:1px;
    margin:1rem 0;
    background:linear-gradient(90deg, rgba(255,255,255,.12), rgba(255,255,255,0));
}

.ct-reveal{
    animation:ctFadeUp .7s ease both;
}

.ct-reveal.delay-1{ animation-delay:.08s; }
.ct-reveal.delay-2{ animation-delay:.16s; }
.ct-reveal.delay-3{ animation-delay:.24s; }

@keyframes ctFadeUp{
    from{
        opacity:0;
        transform:translateY(16px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

@media (max-width: 1199px){
    .ct-grid.four{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }

    .ct-hero-shell,
    .ct-contact-grid,
    .ct-step-grid{
        grid-template-columns:1fr;
    }

    .ct-blog-card.featured{
        grid-column:span 1;
    }

    .ct-grid.three,
    .ct-metric-grid{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px){
    .ct-hero{
        padding:96px 0 26px;
    }

    .ct-hero-main,
    .ct-hero-side,
    .ct-card,
    .ct-price-card,
    .ct-blog-card,
    .ct-step,
    .ct-contact-card{
        padding:1.15rem;
        border-radius:20px;
    }

    .ct-title{
        font-size:2rem;
    }

    .ct-grid.two,
    .ct-grid.three,
    .ct-grid.four,
    .ct-metric-grid,
    .ct-form-grid{
        grid-template-columns:1fr;
    }

    .ct-actions{
        flex-direction:column;
    }

    .ct-button{
        width:100%;
    }
}
</style>
