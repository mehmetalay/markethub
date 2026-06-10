import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../Layouts/AdminLayout';
import type { PageProps } from '../types';

type DashboardProps = PageProps<{
    summary: {
        tenantStatus: string | null;
        roles: string[];
        apiFirst: boolean;
        marketplaceProviders: number;
    };
}>;

export default function Dashboard() {
    const { auth, summary } = usePage<DashboardProps>().props;

    return (
        <AdminLayout title="Kontrol Paneli">
            <Head title="Kontrol Paneli" />

            <div className="grid gap-4 md:grid-cols-4">
                <Metric label="Çalışma Alanı" value={auth.tenant?.name ?? 'Atanmamış'} />
                <Metric label="Durum" value={tenantStatusLabel(summary.tenantStatus)} />
                <Metric label="Roller" value={roleLabels(summary.roles)} />
                <Metric label="Pazaryerleri" value={String(summary.marketplaceProviders)} />
            </div>

            <section className="mt-8 rounded-lg border border-slate-200 bg-white p-6">
                <h2 className="text-lg font-semibold">Platform Özeti</h2>
                <div className="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <p>Çalışma alanı verileri tek veritabanında tenant_id ile ayrıştırılır.</p>
                    <p>Pazaryeri entegrasyonları sağlayıcı sözleşmeleri üzerinden yönetilir.</p>
                    <p>API erişimi Sanctum ile kimlik doğrulamalı olarak hazırlanmıştır.</p>
                    <p>Rol ve yetki yönetimi çalışma alanı bazında yapılandırılmıştır.</p>
                </div>
            </section>
        </AdminLayout>
    );
}

function tenantStatusLabel(status: string | null) {
    const labels: Record<string, string> = {
        active: 'Aktif',
        suspended: 'Askıya Alındı',
        archived: 'Arşivlendi',
    };

    return status ? labels[status] ?? status : 'Yok';
}

function roleLabels(roles: string[]) {
    const labels: Record<string, string> = {
        owner: 'Sahip',
        admin: 'Yönetici',
        operator: 'Operatör',
    };

    return roles.map((role) => labels[role] ?? role).join(', ') || 'Yok';
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5">
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-2 truncate text-xl font-semibold text-slate-950">{value}</p>
        </div>
    );
}
