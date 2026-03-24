@php
    $assistantPage = $assistantPage ?? (request()->route()?->getName() ?? 'public');
@endphp

<style>
.public-assistant{
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1200;
}

.public-assistant .assistant-launcher{
    border: none;
    border-radius: 999px;
    padding: .92rem 1.2rem;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: .7rem;
    font-weight: 800;
    box-shadow: 0 22px 42px rgba(37,99,235,.36);
    transition: transform .2s ease, box-shadow .2s ease;
}

.public-assistant .assistant-launcher:hover{
    transform: translateY(-2px);
    box-shadow: 0 26px 50px rgba(37,99,235,.48);
}

.public-assistant .assistant-launcher-badge{
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.16);
}

.public-assistant .assistant-launcher-badge svg,
.public-assistant .assistant-panel-close svg{
    width: 18px;
    height: 18px;
    stroke: currentColor;
}

.public-assistant .assistant-launcher-copy{
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.1;
}

.public-assistant .assistant-launcher-copy small{
    color: rgba(255,255,255,.75);
    font-size: .72rem;
    font-weight: 600;
}

.public-assistant .assistant-panel{
    position: absolute;
    right: 0;
    bottom: calc(100% + 14px);
    width: min(390px, calc(100vw - 28px));
    max-height: min(76vh, 720px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.08);
    background: linear-gradient(180deg, rgba(15,23,42,.98), rgba(2,6,23,.98));
    box-shadow: 0 30px 80px rgba(0,0,0,.58);
    display: none;
    flex-direction: column;
    transform: translateY(12px) scale(.98);
    opacity: 0;
    transform-origin: bottom right;
    transition: opacity .22s ease, transform .22s ease;
}

.public-assistant .assistant-panel.open{
    display: flex;
    opacity: 1;
    transform: translateY(0) scale(1);
}

.public-assistant .assistant-panel-top{
    padding: 1rem 1rem .95rem;
    background:
        radial-gradient(circle at top left, rgba(56,189,248,.26), transparent 45%),
        linear-gradient(135deg, rgba(37,99,235,.25), rgba(79,70,229,.18));
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.public-assistant .assistant-panel-title{
    display: flex;
    align-items: center;
    gap: .75rem;
}

.public-assistant .assistant-panel-icon{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.09);
    border: 1px solid rgba(255,255,255,.14);
    color: #fff;
}

.public-assistant .assistant-panel-icon svg{
    width: 19px;
    height: 19px;
    stroke: currentColor;
}

.public-assistant .assistant-panel-title h3{
    margin: 0;
    font-size: 1.02rem;
    font-weight: 800;
    color: #fff;
}

.public-assistant .assistant-panel-title p{
    margin: .12rem 0 0;
    color: #cbd5f5;
    font-size: .83rem;
}

.public-assistant .assistant-panel-close{
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
    color: #e5e7eb;
    display: grid;
    place-items: center;
}

.public-assistant .assistant-panel-close:hover{
    background: rgba(255,255,255,.09);
}

.public-assistant .assistant-panel-body{
    padding: .9rem;
    display: grid;
    grid-template-rows: minmax(0, 1fr) auto;
    gap: .7rem;
    flex: 1;
    min-height: 0;
}

.public-assistant .assistant-messages{
    display: flex;
    flex-direction: column;
    gap: .8rem;
    min-height: 240px;
    max-height: min(52vh, 380px);
    flex: 1;
    overflow-y: auto;
    padding-right: .12rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(56,189,248,.35) rgba(255,255,255,.05);
}

.public-assistant .assistant-messages::-webkit-scrollbar{
    width: 8px;
}

.public-assistant .assistant-messages::-webkit-scrollbar-track{
    background: rgba(255,255,255,.04);
    border-radius: 999px;
}

.public-assistant .assistant-messages::-webkit-scrollbar-thumb{
    background: rgba(56,189,248,.32);
    border-radius: 999px;
}

.public-assistant .assistant-msg{
    max-width: 88%;
    padding: .85rem .95rem;
    border-radius: 18px;
    line-height: 1.55;
    font-size: .92rem;
    box-shadow: 0 12px 30px rgba(0,0,0,.18);
}

.public-assistant .assistant-msg a{
    color: #7dd3fc;
    font-weight: 700;
}

.public-assistant .assistant-msg.bot{
    align-self: flex-start;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.07);
    color: #e5e7eb;
}

.public-assistant .assistant-msg.user{
    align-self: flex-end;
    background: linear-gradient(135deg, rgba(37,99,235,.26), rgba(79,70,229,.24));
    border: 1px solid rgba(56,189,248,.18);
    color: #fff;
}

.public-assistant .assistant-msg.typing{
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    min-height: 44px;
}

.public-assistant .assistant-typing-dot{
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,.62);
    animation: publicAssistantTyping 1s infinite ease-in-out;
}

.public-assistant .assistant-typing-dot:nth-child(2){
    animation-delay: .15s;
}

.public-assistant .assistant-typing-dot:nth-child(3){
    animation-delay: .3s;
}

@keyframes publicAssistantTyping{
    0%, 80%, 100%{
        opacity: .35;
        transform: translateY(0);
    }
    40%{
        opacity: 1;
        transform: translateY(-3px);
    }
}

.public-assistant .assistant-footer{
    display:flex;
    flex-direction:column;
    gap:.65rem;
    padding-top:.15rem;
    border-top:1px solid rgba(255,255,255,.06);
}

.public-assistant .assistant-chip-list{
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    max-height: 92px;
    overflow-y: auto;
    padding-right: .14rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(56,189,248,.28) rgba(255,255,255,.04);
    -webkit-overflow-scrolling: touch;
}

.public-assistant .assistant-chip-list::-webkit-scrollbar{
    width: 6px;
}

.public-assistant .assistant-chip-list::-webkit-scrollbar-track{
    background: rgba(255,255,255,.04);
    border-radius: 999px;
}

.public-assistant .assistant-chip-list::-webkit-scrollbar-thumb{
    background: rgba(56,189,248,.28);
    border-radius: 999px;
}

.public-assistant .assistant-chip{
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: #dbeafe;
    border-radius: 999px;
    padding: .42rem .72rem;
    font-size: .75rem;
    font-weight: 700;
    flex: 0 0 auto;
    white-space: nowrap;
}

.public-assistant .assistant-chip:hover{
    background: rgba(56,189,248,.10);
    border-color: rgba(56,189,248,.18);
    color: #fff;
}

.public-assistant .assistant-form{
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .65rem;
}

.public-assistant .assistant-input{
    min-height: 48px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: #fff;
    padding: .85rem .95rem;
}

.public-assistant .assistant-input::placeholder{
    color: rgba(255,255,255,.38);
}

.public-assistant .assistant-input:focus{
    outline: none;
    border-color: rgba(56,189,248,.3);
    box-shadow: 0 0 0 .2rem rgba(56,189,248,.10);
}

.public-assistant .assistant-send{
    min-width: 104px;
    border: none;
    border-radius: 15px;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    font-weight: 800;
    padding: .8rem 1rem;
}

@media (max-width: 575px){
    .public-assistant{
        right: 12px;
        bottom: 10px;
        left: auto;
    }

    .public-assistant .assistant-launcher{
        width: 54px;
        height: 54px;
        padding: 0;
        border-radius: 50%;
        justify-content: center;
        box-shadow: 0 18px 34px rgba(37,99,235,.30);
    }

    .public-assistant .assistant-launcher-badge{
        width: 38px;
        height: 38px;
    }

    .public-assistant .assistant-launcher-copy{
        display: none;
    }

    .public-assistant .assistant-panel{
        right: 0;
        left: auto;
        width: min(360px, calc(100vw - 18px));
        bottom: calc(100% + 10px);
    }

    .public-assistant .assistant-panel-body{
        padding: .8rem;
        gap: .65rem;
    }

    .public-assistant .assistant-messages{
        max-height: min(46vh, 320px);
        padding-right: 0;
    }

    .public-assistant .assistant-footer{
        gap: .55rem;
    }

    .public-assistant .assistant-chip-list{
        max-height: 80px;
        gap: .42rem;
    }

    .public-assistant .assistant-chip{
        font-size: .72rem;
        padding: .38rem .68rem;
    }

    .public-assistant .assistant-form{
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .5rem;
    }

    .public-assistant .assistant-input{
        min-height: 44px;
        padding: .78rem .85rem;
        font-size: .88rem;
    }

    .public-assistant .assistant-send{
        width: auto;
        min-width: 84px;
        padding: .75rem .9rem;
    }
}
</style>

<div class="public-assistant" id="publicAssistant" data-page="{{ $assistantPage }}">
    <button type="button" class="assistant-launcher" id="publicAssistantLauncher" aria-expanded="false" aria-controls="publicAssistantPanel">
        <span class="assistant-launcher-badge" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M7 9h10"></path>
                <path d="M7 13h7"></path>
                <path d="M12 3C7.03 3 3 6.58 3 11c0 2.22 1.02 4.23 2.69 5.68L5 21l4.76-2.05A11.06 11.06 0 0 0 12 19c4.97 0 9-3.58 9-8s-4.03-8-9-8Z"></path>
            </svg>
        </span>
        <span class="assistant-launcher-copy">
            Need help?
            <small>Ask the CleanTech assistant</small>
        </span>
    </button>

    <div class="assistant-panel" id="publicAssistantPanel" aria-live="polite" hidden>
        <div class="assistant-panel-top">
            <div class="assistant-panel-title">
                <div class="assistant-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 8V4"></path>
                        <path d="M9 2h6"></path>
                        <rect x="4" y="8" width="16" height="11" rx="4"></rect>
                        <path d="M9 13h.01"></path>
                        <path d="M15 13h.01"></path>
                        <path d="M8 19v2"></path>
                        <path d="M16 19v2"></path>
                    </svg>
                </div>
                <div>
                    <h3>CleanTech Assistant</h3>
                    <p>Quick answers for booking, provider sign-up, pricing, and support.</p>
                </div>
            </div>

            <button type="button" class="assistant-panel-close" id="publicAssistantClose" aria-label="Close assistant">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18"></path>
                    <path d="M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="assistant-panel-body">
            <div class="assistant-messages" id="publicAssistantMessages"></div>

            <div class="assistant-footer">
                <div class="assistant-chip-list" id="publicAssistantSuggestions"></div>

                <form class="assistant-form" id="publicAssistantForm">
                    <input
                        type="text"
                        class="assistant-input"
                        id="publicAssistantInput"
                        placeholder="Ask about booking, provider sign-up, pricing, or support"
                        autocomplete="off"
                    >
                    <button type="submit" class="assistant-send">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const assistantRoot = document.getElementById('publicAssistant');
    const launcher = document.getElementById('publicAssistantLauncher');
    const panel = document.getElementById('publicAssistantPanel');
    const closeButton = document.getElementById('publicAssistantClose');
    const messages = document.getElementById('publicAssistantMessages');
    const form = document.getElementById('publicAssistantForm');
    const input = document.getElementById('publicAssistantInput');
    const suggestions = document.getElementById('publicAssistantSuggestions');

    if (!assistantRoot || !launcher || !panel || !messages || !form || !input || !suggestions) {
        return;
    }

    const currentPage = assistantRoot.dataset.page || 'public';

    const routes = {
        home: @json(route('home')),
        about: @json(route('about')),
        services: @json(route('services')),
        howItWorks: @json(route('how.it.works')),
        pricing: @json(route('pricing')),
        blog: @json(route('blog')),
        contact: @json(route('contact')),
        faq: @json(route('faq')),
        customerRegister: @json(route('customer.register')),
        customerLogin: @json(route('customer.login')),
        providerSignup: @json(route('provider.pre_register.terms')),
        providerLogin: @json(route('provider.login')),
        email: 'mailto:janzedoysabas@gmail.com',
    };

    const pageIntentMap = {
        home: 'default',
        services: 'services',
        pricing: 'pricing',
        contact: 'support',
        faq: 'support',
        about: 'company',
        blog: 'company',
        'how.it.works': 'booking',
    };

    const suggestionSets = {
        default: [
            'What is CleanTech?',
            'How do I book a service?',
            'How do I register as a provider?',
            'What services do you offer?',
            'How does pricing work?',
            'How can I contact CleanTech?',
        ],
        booking: [
            'How do I choose a provider?',
            'How do available dates work?',
            'Can I cancel a booking?',
            'Can I track my provider?',
        ],
        provider: [
            'How do I register as a provider?',
            'How does provider approval work?',
            'How do providers receive bookings?',
            'Where do providers log in?',
        ],
        services: [
            'What services do you offer?',
            'How do I book a service?',
            'How do I choose a provider?',
        ],
        pricing: [
            'How does pricing work?',
            'Do providers set the price?',
            'What affects the total amount?',
        ],
        support: [
            'How can I contact CleanTech?',
            'Where can I read FAQs?',
            'How do I sign in?',
        ],
        account: [
            'How do I create a customer account?',
            'Where do providers log in?',
            'How do I book a service?',
        ],
        company: [
            'What is CleanTech?',
            'How does CleanTech work?',
            'How can I contact CleanTech?',
        ],
        tracking: [
            'Can I track my provider?',
            'How do available dates work?',
            'Can I cancel a booking?',
        ],
    };

    const state = {
        lastIntent: pageIntentMap[currentPage] || 'default',
    };

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^\w\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function tokenizeText(value) {
        return normalizeText(value)
            .split(' ')
            .filter((token) => token.length > 2);
    }

    function hasAny(text, parts) {
        return parts.some((part) => text.includes(part));
    }

    function hasEveryGroup(text, groups) {
        return groups.every((group) => group.some((part) => text.includes(part)));
    }

    const knowledgeBase = [
        {
            intentId: 'company',
            keywords: ['what is cleantech', 'what is clean tech', 'who is cleantech', 'who are you', 'what does cleantech do'],
            tokens: ['cleantech', 'platform', 'customers', 'providers', 'booking', 'services'],
            message: `CleanTech is a service platform that helps customers book home cleaning more easily and helps approved providers receive and manage bookings in one place. Customers can create an account, choose a service, pick an available provider, confirm the booking, and follow updates from their dashboard.`,
        },
        {
            intentId: 'company',
            keywords: ['mission', 'vision', 'goal', 'purpose'],
            tokens: ['mission', 'vision', 'trusted', 'affordable', 'reliable', 'community'],
            message: `CleanTech aims to provide reliable and affordable home cleaning, repair, and maintenance services with honesty, convenience, and customer care at the center. Its vision is to be a trusted and accessible service provider in the community, known for dependable work and practical support for households.`,
        },
        {
            intentId: 'services',
            keywords: ['services', 'what services', 'offer', 'general cleaning', 'deep cleaning', 'specific area'],
            tokens: ['services', 'cleaning', 'general', 'deep', 'specific', 'area'],
            message: `CleanTech currently highlights General Cleaning, Deep Cleaning, and Specific Area Cleaning on the public pages. You can compare them on the <a href="${routes.services}">Services page</a> and then view available providers for your chosen date.`,
        },
        {
            intentId: 'booking',
            keywords: ['book a service', 'how do i book', 'how to book', 'booking steps', 'reserve service', 'schedule service'],
            tokens: ['book', 'booking', 'service', 'provider', 'date', 'confirm'],
            message: `To book, create a customer account, sign in, open the services page, choose a service, tap View Providers, pick an available provider for your date, fill in the booking details, and confirm the request. <a href="${routes.customerRegister}">Start here</a>.`,
        },
        {
            intentId: 'provider',
            keywords: ['register as a provider', 'provider sign up', 'provider signup', 'become a provider', 'apply as provider'],
            tokens: ['provider', 'register', 'signup', 'approval', 'application'],
            message: `To become a provider, open <a href="${routes.providerSignup}">Provider Sign-up</a>, review the terms, continue the registration steps, complete the required details, and wait for approval before receiving bookings.`,
        },
        {
            intentId: 'provider',
            keywords: ['provider approval', 'approved provider', 'provider review', 'how provider approval works'],
            tokens: ['provider', 'approval', 'approved', 'review'],
            message: `Providers appear on the platform only after review and approval. Once approved, they can log in, set availability, and receive customer bookings.`,
        },
        {
            intentId: 'account',
            keywords: ['customer account', 'create account', 'customer register', 'customer signup', 'sign in', 'login'],
            tokens: ['customer', 'account', 'register', 'login', 'provider'],
            message: `Customers can create an account from <a href="${routes.customerRegister}">Customer Registration</a> and sign in from <a href="${routes.customerLogin}">Customer Login</a>. Providers use <a href="${routes.providerSignup}">Provider Sign-up</a> first, then <a href="${routes.providerLogin}">Provider Login</a> after approval.`,
        },
        {
            intentId: 'pricing',
            keywords: ['pricing', 'price', 'how much', 'cost', 'fee', 'amount', 'payment'],
            tokens: ['price', 'pricing', 'amount', 'service', 'option'],
            message: `Pricing depends on the service type and the option you pick. The <a href="${routes.pricing}">Pricing page</a> gives the overview, and the booking flow shows the total before you confirm.`,
        },
        {
            intentId: 'booking',
            keywords: ['available date', 'availability', 'time slot', 'available providers', 'today', 'tomorrow'],
            tokens: ['available', 'date', 'slot', 'provider', 'time'],
            message: `Availability is based on the exact date you choose. When you change the date, CleanTech refreshes the providers and available time slots for that day only.`,
        },
        {
            intentId: 'tracking',
            keywords: ['track provider', 'tracking', 'live location', 'provider location'],
            tokens: ['tracking', 'provider', 'location', 'live'],
            message: `Customers can follow provider live tracking during active bookings. Tracking is meant for the in-progress stage so customers can see the provider while the job is already underway.`,
        },
        {
            intentId: 'booking',
            keywords: ['cancel booking', 'cancellation', 'can i cancel', 'reschedule'],
            tokens: ['cancel', 'booking', 'cancellation', 'progress'],
            message: `Customers can cancel eligible bookings before the job is already in progress. Once work has started, customer-side cancellation is locked.`,
        },
        {
            intentId: 'booking',
            keywords: ['pin location', 'service location', 'map', 'address', 'barangay', 'location'],
            tokens: ['map', 'pin', 'location', 'address', 'barangay'],
            message: `During booking, you can search the place, pin the service location on the map, and continue with the rest of the booking details before confirmation.`,
        },
        {
            intentId: 'support',
            keywords: ['contact', 'support', 'email', 'reach cleantech', 'message cleantech'],
            tokens: ['contact', 'support', 'email', 'help'],
            message: `You can reach CleanTech through the <a href="${routes.contact}">Contact page</a> or email directly at <a href="${routes.email}">janzedoysabas@gmail.com</a>.`,
        },
        {
            intentId: 'support',
            keywords: ['faq', 'questions', 'help page'],
            tokens: ['faq', 'questions', 'answers', 'help'],
            message: `The <a href="${routes.faq}">FAQ page</a> is the fastest place for common questions about booking, providers, and how the platform works.`,
        },
        {
            intentId: 'booking',
            keywords: ['how it works', 'how cleantech works', 'how does cleantech work', 'how does clean tech work', 'how does it work'],
            tokens: ['how', 'work', 'works', 'cleantech', 'book', 'service', 'provider'],
            message: `CleanTech works in a simple flow: create an account, choose a service, pick an available provider for your date, confirm the booking, and follow updates from your dashboard. <a href="${routes.howItWorks}">See how it works</a>.`,
        },
    ];

    function scoreKnowledgeEntry(text, entry) {
        let score = 0;

        (entry.keywords || []).forEach((keyword) => {
            if (text.includes(keyword)) {
                score += keyword.split(' ').length > 2 ? 5 : 3;
            }
        });

        const tokens = tokenizeText(text);
        const tokenMatches = tokens.filter((token) => (entry.tokens || []).includes(token));
        score += tokenMatches.length;

        return score;
    }

    function answerFromKnowledgeBase(questionText) {
        let bestEntry = null;
        let bestScore = 0;

        knowledgeBase.forEach((entry) => {
            const score = scoreKnowledgeEntry(questionText, entry);
            if (score > bestScore) {
                bestScore = score;
                bestEntry = entry;
            }
        });

        if (!bestEntry || bestScore < 2) {
            return null;
        }

        return reply(bestEntry.intentId, bestEntry.message);
    }

    function renderSuggestions(intentId) {
        const list = suggestionSets[intentId] || suggestionSets.default;

        suggestions.innerHTML = list.map((question) => {
            return `<button type="button" class="assistant-chip" data-question="${question.replace(/"/g, '&quot;')}">${question}</button>`;
        }).join('');

        suggestions.querySelectorAll('.assistant-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                openAssistant();
                handleQuestion(chip.dataset.question || chip.textContent || '');
            });
        });
    }

    function addMessage(content, role) {
        const bubble = document.createElement('div');
        bubble.className = `assistant-msg ${role}`;
        bubble.innerHTML = content;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function addTyping() {
        const bubble = document.createElement('div');
        bubble.className = 'assistant-msg bot typing';
        bubble.innerHTML = '<span class="assistant-typing-dot"></span><span class="assistant-typing-dot"></span><span class="assistant-typing-dot"></span>';
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }

    function reply(intentId, message) {
        state.lastIntent = intentId;
        return { intentId, message };
    }

    function answerGreeting() {
        return reply(
            state.lastIntent || 'default',
            'Hello. I can help with booking, services, provider sign-up, pricing, tracking, cancellations, and contact details.'
        );
    }

    function answerThanks() {
        return reply(
            state.lastIntent || 'default',
            'You are welcome. If you need another CleanTech detail, just ask.'
        );
    }

    function answerPlatformOverview() {
        return reply(
            'company',
            `CleanTech is a local service platform that helps customers book home cleaning more easily and helps approved providers manage bookings in one place. Customers can create an account, choose a service, pick an available provider, confirm the booking, and follow updates from their dashboard.`
        );
    }

    function answerProviderSignup() {
        return reply(
            'provider',
            `To register as a provider, open <a href="${routes.providerSignup}">Provider Sign-up</a>, review and accept the terms, continue the pre-registration form, verify your account, then finish the provider registration details. After that, wait for approval before receiving bookings.`
        );
    }

    function answerProviderApproval() {
        return reply(
            'provider',
            `Providers appear as approved only after review. Once approved, they can log in, manage availability, and receive customer bookings. <a href="${routes.providerSignup}">Start the provider application here</a>.`
        );
    }

    function answerProviderJobs() {
        return reply(
            'provider',
            `Approved providers receive bookings when customers choose them from the available provider list for a selected date. Providers then manage the booking from their provider workspace.`
        );
    }

    function answerAccount(questionText) {
        if (hasAny(questionText, ['provider login', 'provider sign in', 'where provider log in'])) {
            return reply(
                'account',
                `Providers can sign in from <a href="${routes.providerLogin}">Provider Login</a>. If you are not registered yet, start from <a href="${routes.providerSignup}">Provider Sign-up</a>.`
            );
        }

        return reply(
            'account',
            `Customers can create an account from <a href="${routes.customerRegister}">Customer Registration</a> and sign in from <a href="${routes.customerLogin}">Customer Login</a>. Providers should use <a href="${routes.providerSignup}">Provider Sign-up</a> first, then <a href="${routes.providerLogin}">Provider Login</a> after approval.`
        );
    }

    function answerBooking() {
        return reply(
            'booking',
            `To book a service, create a customer account, sign in, open the services page, choose a service, tap View Providers, pick an available provider for your selected date, then fill in the booking details and confirm. <a href="${routes.customerRegister}">Start here</a>.`
        );
    }

    function answerChooseProvider() {
        return reply(
            'booking',
            `When you choose a service and date, CleanTech shows the providers available for that exact day. You can open a provider profile first or continue directly to booking.`
        );
    }

    function answerServices() {
        return reply(
            'services',
            `CleanTech currently highlights General Cleaning, Deep Cleaning, and Specific Area Cleaning on the public site. You can compare them on the <a href="${routes.services}">Services page</a>.`
        );
    }

    function answerPricing(questionText) {
        if (hasEveryGroup(questionText, [['provider'], ['set', 'sets', 'choose', 'chooses']])) {
            return reply(
                'pricing',
                `Providers do not manually type a random price on the public pages. The total depends on the service and selected option, and the booking flow shows the amount before confirmation.`
            );
        }

        return reply(
            'pricing',
            `Pricing depends on the service type and selected option. The public <a href="${routes.pricing}">Pricing page</a> gives the overview, and the booking flow shows the total before you confirm.`
        );
    }

    function answerAvailability() {
        return reply(
            'booking',
            `Availability is tied to the exact date you pick. When you change the date, CleanTech refreshes the available providers and time slots for that day only.`
        );
    }

    function answerCancellation() {
        return reply(
            'booking',
            `Customers can cancel eligible bookings before the job is already in progress. Once work has started, customer-side cancellation is locked.`
        );
    }

    function answerTracking() {
        return reply(
            'tracking',
            `Live provider tracking shows up during active bookings so customers can follow the provider while the job is in progress. It does not need to run early while the booking is only confirmed.`
        );
    }

    function answerLocation() {
        return reply(
            'booking',
            `During booking, you can pin the service location on the map, search the address, and fill in the rest of the booking details before confirmation.`
        );
    }

    function answerSupport() {
        return reply(
            'support',
            `You can reach CleanTech through the <a href="${routes.contact}">Contact page</a> or email directly at <a href="${routes.email}">janzedoysabas@gmail.com</a>.`
        );
    }

    function answerFaq() {
        return reply(
            'support',
            `The <a href="${routes.faq}">FAQ page</a> is the quickest place for common questions about booking, providers, and how the platform works.`
        );
    }

    function answerAbout() {
        return reply(
            'company',
            `CleanTech focuses on reliable and affordable household services with honesty, convenience, and customer care. You can read more on the <a href="${routes.about}">About page</a>.`
        );
    }

    function answerHowItWorks() {
        return reply(
            'booking',
            `CleanTech works in a simple flow: create an account, choose a service, pick an available provider for your date, confirm the booking details, and follow the booking from your customer side pages. <a href="${routes.howItWorks}">See how it works</a>.`
        );
    }

    function getReply(question) {
        const normalized = normalizeText(question);

        if (!normalized) {
            return reply(
                state.lastIntent || 'default',
                'Ask me about booking, services, provider sign-up, pricing, tracking, or contact details.'
            );
        }

        if (hasAny(normalized, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
            return answerGreeting();
        }

        if (hasAny(normalized, ['thank you', 'thanks', 'salamat', 'ty'])) {
            return answerThanks();
        }

        if (hasAny(normalized, ['what can you do', 'what do you do', 'functions', 'features', 'what is cleantech', 'what is clean tech'])) {
            return answerPlatformOverview();
        }

        if (hasEveryGroup(normalized, [['provider', 'cleaner'], ['register', 'registration', 'sign up', 'signup', 'apply', 'join', 'become']])) {
            return answerProviderSignup();
        }

        if (hasEveryGroup(normalized, [['provider', 'cleaner'], ['approval', 'approved', 'review', 'verified']])) {
            return answerProviderApproval();
        }

        if (hasEveryGroup(normalized, [['provider', 'cleaner'], ['booking', 'bookings', 'jobs', 'receive', 'get booked']])) {
            return answerProviderJobs();
        }

        if (hasEveryGroup(normalized, [['customer', 'account'], ['register', 'registration', 'sign up', 'signup', 'create']])) {
            return answerAccount(normalized);
        }

        if (hasAny(normalized, ['login', 'log in', 'sign in', 'account access', 'provider login', 'customer login'])) {
            return answerAccount(normalized);
        }

        if (hasAny(normalized, ['how do i book', 'book a service', 'booking steps', 'reserve service', 'schedule service'])) {
            return answerBooking();
        }

        if (hasAny(normalized, ['choose a provider', 'pick a provider', 'available provider', 'view providers'])) {
            return answerChooseProvider();
        }

        if (hasAny(normalized, ['service', 'services', 'general cleaning', 'deep cleaning', 'specific area'])) {
            return answerServices();
        }

        if (hasAny(normalized, ['price', 'pricing', 'cost', 'fee', 'fees', 'how much', 'payment', 'amount'])) {
            return answerPricing(normalized);
        }

        if (hasAny(normalized, ['available', 'availability', 'slot', 'time slot', 'date', 'today', 'tomorrow'])) {
            return answerAvailability();
        }

        if (hasAny(normalized, ['cancel', 'cancellation', 'reschedule'])) {
            return answerCancellation();
        }

        if (hasAny(normalized, ['track', 'tracking', 'live location', 'provider location'])) {
            return answerTracking();
        }

        if (hasAny(normalized, ['map', 'pin location', 'service location', 'address', 'barangay', 'location'])) {
            return answerLocation();
        }

        if (hasAny(normalized, ['contact', 'email', 'support', 'phone', 'reach', 'message'])) {
            return answerSupport();
        }

        if (hasAny(normalized, ['how does cleantech work', 'how cleantech works', 'how does clean tech work', 'how does it work'])) {
            return answerHowItWorks();
        }

        if (hasAny(normalized, ['faq', 'questions', 'how it works'])) {
            return answerFaq();
        }

        if (hasAny(normalized, ['about', 'mission', 'vision', 'company'])) {
            return answerAbout();
        }

        if (state.lastIntent === 'provider' && hasAny(normalized, ['next', 'after', 'then what', 'what happens next'])) {
            return answerProviderApproval();
        }

        if (state.lastIntent === 'booking' && hasAny(normalized, ['after that', 'what next', 'then what'])) {
            return answerChooseProvider();
        }

        const knowledgeReply = answerFromKnowledgeBase(normalized);
        if (knowledgeReply) {
            return knowledgeReply;
        }

        return reply(
            state.lastIntent || 'default',
            `I can help with CleanTech website questions, booking steps, provider sign-up, services, pricing, tracking, and support. If your question is outside the site flow, I may not answer it well. Try one of the quick questions below, open the <a href="${routes.faq}">FAQ</a>, or email <a href="${routes.email}">janzedoysabas@gmail.com</a>.`
        );
    }

    function openAssistant() {
        panel.hidden = false;
        requestAnimationFrame(() => panel.classList.add('open'));
        launcher.setAttribute('aria-expanded', 'true');
    }

    function closeAssistant() {
        panel.classList.remove('open');
        launcher.setAttribute('aria-expanded', 'false');

        setTimeout(() => {
            if (!panel.classList.contains('open')) {
                panel.hidden = true;
            }
        }, 220);
    }

    function handleQuestion(question) {
        const cleaned = String(question || '').trim();

        if (!cleaned) {
            return;
        }

        addMessage(cleaned, 'user');
        const typing = addTyping();
        const answer = getReply(cleaned);

        setTimeout(() => {
            typing.remove();
            addMessage(answer.message, 'bot');
            renderSuggestions(answer.intentId || 'default');
        }, 260);
    }

    addMessage('Hello. I am the CleanTech assistant. Ask me about CleanTech, booking, provider sign-up, services, pricing, tracking, or support.', 'bot');
    renderSuggestions(pageIntentMap[currentPage] || 'default');

    launcher.addEventListener('click', () => {
        if (panel.classList.contains('open')) {
            closeAssistant();
            return;
        }

        openAssistant();
        input.focus();
    });

    closeButton.addEventListener('click', () => {
        closeAssistant();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        handleQuestion(input.value);
        input.value = '';
        input.focus();
    });

    document.addEventListener('click', (event) => {
        if (!panel.classList.contains('open')) {
            return;
        }

        if (!assistantRoot.contains(event.target)) {
            closeAssistant();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAssistant();
        }
    });
})();
</script>
