import { Link, router, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import type { PageProps } from '../types';

type AdminLayoutProps = PropsWithChildren<{
    title: string;
}>;

export default function AdminLayout({ children, title }: AdminLayoutProps) {
    const page = usePage<PageProps>();
    const { auth } = page.props;
    const navigation = [
        { label: 'Kontrol Paneli', href: '/dashboard' },
        { label: 'Ürünler', href: '/products' },
        { label: 'İlanlar', href: '/listings' },
        { label: 'Pazaryeri Eşleştirmeleri', href: '/marketplace-mappings' },
        { label: 'Pazaryeri Hesapları', href: '/marketplace-accounts' },
    ];

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:gap-8">
                        <div>
                            <Link href="/dashboard" className="text-lg font-semibold">
                                MarketHub
                            </Link>
                            <p className="text-sm text-slate-500">
                                {auth.tenant?.name ?? 'Çalışma alanı seçilmedi'}
                            </p>
                        </div>

                        <nav className="flex flex-wrap gap-2">
                            {navigation.map((item) => {
                                const isActive =
                                    page.url === item.href || (item.href !== '/dashboard' && page.url.startsWith(item.href));

                                return (
                                    <Link
                                        key={item.href}
                                        className={`rounded-md px-3 py-2 text-sm font-medium ${
                                            isActive
                                                ? 'bg-blue-50 text-blue-700'
                                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'
                                        }`}
                                        href={item.href}
                                    >
                                        {item.label}
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="text-right">
                            <p className="text-sm font-medium">{auth.user?.name}</p>
                            <p className="text-xs text-slate-500">{auth.user?.email}</p>
                        </div>
                        <button
                            className="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100"
                            type="button"
                            onClick={() => router.post('/logout')}
                        >
                            Çıkış Yap
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-6 py-8">
                <h1 className="mb-6 text-2xl font-semibold">{title}</h1>
                {children}
            </main>
        </div>
    );
}
