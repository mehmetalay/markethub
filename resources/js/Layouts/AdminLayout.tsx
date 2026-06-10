import { Link, router, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import type { PageProps } from '../types';

type AdminLayoutProps = PropsWithChildren<{
    title: string;
}>;

export default function AdminLayout({ children, title }: AdminLayoutProps) {
    const { auth } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <div>
                        <Link href="/dashboard" className="text-lg font-semibold">
                            MarketHub
                        </Link>
                        <p className="text-sm text-slate-500">
                            {auth.tenant?.name ?? 'Çalışma alanı seçilmedi'}
                        </p>
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
