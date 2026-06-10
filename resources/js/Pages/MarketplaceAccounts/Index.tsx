import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import type { PageProps } from '../../types';

type MarketplaceAccount = {
    id: number;
    name: string;
    status: string;
    created_at: string | null;
    marketplace: {
        id: number;
        code: string;
        name: string;
    };
};

type MarketplaceAccountsIndexProps = PageProps<{
    accounts: MarketplaceAccount[];
}>;

export default function Index() {
    const { accounts } = usePage<MarketplaceAccountsIndexProps>().props;

    return (
        <AdminLayout title="Pazaryeri Hesapları">
            <Head title="Pazaryeri Hesapları" />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="max-w-2xl text-sm text-slate-600">
                    Çalışma alanı bazlı pazaryeri hesaplarını yönetin. Kimlik bilgileri güvenli şekilde saklanır ve ekranda gösterilmez.
                </p>
                <Link
                    className="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    href="/marketplace-accounts/create"
                >
                    Yeni Hesap Oluştur
                </Link>
            </div>

            <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {accounts.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-200 bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Hesap Adı</th>
                                    <th className="px-4 py-3 font-medium">Pazaryeri</th>
                                    <th className="px-4 py-3 font-medium">Kod</th>
                                    <th className="px-4 py-3 font-medium">Durum</th>
                                    <th className="px-4 py-3 font-medium">Oluşturulma</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {accounts.map((account) => (
                                    <tr key={account.id}>
                                        <td className="px-4 py-3 font-medium text-slate-950">{account.name}</td>
                                        <td className="px-4 py-3 text-slate-700">{account.marketplace.name}</td>
                                        <td className="px-4 py-3 text-slate-700">{account.marketplace.code}</td>
                                        <td className="px-4 py-3 text-slate-700">{accountStatusLabel(account.status)}</td>
                                        <td className="px-4 py-3 text-slate-700">{formatDate(account.created_at)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="px-6 py-10 text-center">
                        <h2 className="text-lg font-semibold text-slate-950">Henüz pazaryeri hesabı yok</h2>
                        <p className="mt-2 text-sm text-slate-600">
                            İlk pazaryeri hesabınızı oluşturarak çalışma alanı bazlı hesap yönetimine başlayabilirsiniz.
                        </p>
                        <Link
                            className="mt-5 inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            href="/marketplace-accounts/create"
                        >
                            Hesap Oluştur
                        </Link>
                    </div>
                )}
            </section>
        </AdminLayout>
    );
}

function accountStatusLabel(status: string) {
    const labels: Record<string, string> = {
        draft: 'Taslak',
        active: 'Aktif',
        inactive: 'Pasif',
    };

    return labels[status] ?? status;
}

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('tr-TR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
