import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { auth, tenant, flash } = usePage().props;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {tenant ? `Dashboard - ${tenant.name}` : 'Dashboard'}
                    </h2>
                    {tenant && (
                        <Link
                            href={route('tenant.settings.edit')}
                            className="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none"
                        >
                            ⚙️ Pengaturan Lembaga
                        </Link>
                    )}
                </div>
            }
        >
            <Head title={tenant ? `${tenant.name} - Dashboard` : 'Dashboard'} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                    {flash?.status && (
                        <div className="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 shadow-sm">
                            {flash.status}
                        </div>
                    )}

                    {tenant ? (
                        <>
                            {/* Tenant Identity Card */}
                            <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                                <div
                                    className="h-4 w-full"
                                    style={{ backgroundColor: tenant.primary_color || '#2563eb' }}
                                />
                                <div className="p-6 sm:p-8">
                                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div className="flex items-center space-x-4">
                                            {tenant.logo_url ? (
                                                <img
                                                    src={tenant.logo_url}
                                                    alt={tenant.name}
                                                    className="h-16 w-16 rounded-xl border border-gray-200 object-cover p-1 shadow-sm"
                                                />
                                            ) : (
                                                <div
                                                    className="flex h-16 w-16 items-center justify-center rounded-xl text-xl font-bold text-white shadow-sm"
                                                    style={{ backgroundColor: tenant.primary_color || '#2563eb' }}
                                                >
                                                    {tenant.name.substring(0, 2).toUpperCase()}
                                                </div>
                                            )}
                                            <div>
                                                <h1 className="text-2xl font-bold text-gray-900">
                                                    {tenant.name}
                                                </h1>
                                                <p className="text-sm font-medium text-blue-600">
                                                    Subdomain: <span className="font-mono">{tenant.slug}</span>
                                                </p>
                                                {tenant.description && (
                                                    <p className="mt-1 text-sm text-gray-600">
                                                        {tenant.description}
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex flex-col sm:items-end gap-1 text-xs text-gray-500">
                                            {tenant.phone && (
                                                <p>📞 WhatsApp: <strong className="text-gray-700">{tenant.phone}</strong></p>
                                            )}
                                            {tenant.address && (
                                                <p>📍 Alamat: <span className="text-gray-700">{tenant.address}</span></p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* MVP Pilot Quick Overview */}
                            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <p className="text-xs font-medium text-gray-500">Status Lembaga</p>
                                    <p className="mt-2 text-xl font-bold text-green-600">Aktif & Siap</p>
                                </div>
                                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <p className="text-xs font-medium text-gray-500">Role Anda</p>
                                    <p className="mt-2 text-xl font-bold text-gray-900">Pemilik Lembaga (Owner)</p>
                                </div>
                                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <p className="text-xs font-medium text-gray-500">Portal Siswa</p>
                                    <p className="mt-2 text-sm font-mono text-gray-700">/{tenant.slug}</p>
                                </div>
                                <div className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                                    <p className="text-xs font-medium text-gray-500">Branding Lembaga</p>
                                    <div className="mt-2 flex items-center space-x-2">
                                        <span
                                            className="h-4 w-4 rounded-full border border-gray-300"
                                            style={{ backgroundColor: tenant.primary_color || '#2563eb' }}
                                        />
                                        <span className="text-sm font-semibold text-gray-700">
                                            {tenant.primary_color || '#2563eb'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </>
                    ) : (
                        <div className="rounded-2xl border border-blue-200 bg-blue-50 p-8 text-center shadow-sm">
                            <h3 className="text-xl font-bold text-blue-900">
                                Selamat Datang! Anda Belum Memiliki Lembaga Bimbingan Belajar
                            </h3>
                            <p className="mx-auto mt-2 max-w-xl text-sm text-blue-700">
                                Untuk mulai mengelola siswa, tutor, kelas, dan jadwal bimbingan belajar, silakan lakukan onboarding dan buat profil LBB Anda terlebih dahulu.
                            </p>
                            <div className="mt-6">
                                <Link
                                    href={route('onboarding.create')}
                                    className="inline-flex items-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none"
                                >
                                    🚀 Mulai Onboarding LBB Sekarang
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
