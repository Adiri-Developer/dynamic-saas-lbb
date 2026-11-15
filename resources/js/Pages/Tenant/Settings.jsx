import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

const PRESET_COLORS = [
    { label: 'Biru Utama', hex: '#2563eb' },
    { label: 'Indigo', hex: '#4f46e5' },
    { label: 'Emerald Hijau', hex: '#059669' },
    { label: 'Ungu Edukasi', hex: '#7c3aed' },
    { label: 'Amber Hangat', hex: '#d97706' },
    { label: 'Rose Merah', hex: '#e11d48' },
];

export default function TenantSettings({ tenant }) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        name: tenant?.name || '',
        phone: tenant?.phone || '',
        address: tenant?.address || '',
        description: tenant?.description || '',
        logo: null,
        primary_color: tenant?.primary_color || '#2563eb',
    });

    const [logoPreview, setLogoPreview] = useState(tenant?.logo_url || null);

    const handleLogoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('logo', file);
            setLogoPreview(URL.createObjectURL(file));
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('tenant.settings.update'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Pengaturan Profil Lembaga ({tenant?.name})
                </h2>
            }
        >
            <Head title={`Pengaturan Profil - ${tenant?.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm sm:p-10">
                        <div className="mb-8 border-b border-gray-100 pb-6">
                            <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                                Profil & Branding Lembaga
                            </h1>
                            <p className="mt-1 text-sm text-gray-600">
                                Perbarui identitas visual, informasi kontak, dan deskripsi profil bimbingan belajar Anda.
                            </p>
                        </div>

                        {flash?.status && (
                            <div className="mb-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800">
                                {flash.status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6">
                            {/* Subdomain (Read Only) */}
                            <div>
                                <InputLabel value="Subdomain Lembaga" />
                                <div className="mt-1 flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700 border border-gray-200">
                                    <span className="font-semibold text-gray-900">{tenant?.slug}</span>
                                    <span className="ml-2 text-xs text-gray-500">
                                        (Subdomain sistem bersifat tetap)
                                    </span>
                                </div>
                            </div>

                            {/* Nama Lembaga */}
                            <div>
                                <InputLabel htmlFor="name" value="Nama Lembaga Bimbingan Belajar *" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={data.name}
                                    className="mt-1 block w-full"
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            {/* Deskripsi */}
                            <div>
                                <InputLabel htmlFor="description" value="Deskripsi Singkat / Tagline" />
                                <textarea
                                    id="description"
                                    rows="3"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <InputError message={errors.description} className="mt-2" />
                            </div>

                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                {/* Nomor WhatsApp */}
                                <div>
                                    <InputLabel htmlFor="phone" value="No. WhatsApp / Telepon Resmi" />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        name="phone"
                                        value={data.phone}
                                        className="mt-1 block w-full"
                                        onChange={(e) => setData('phone', e.target.value)}
                                    />
                                    <InputError message={errors.phone} className="mt-2" />
                                </div>

                                {/* Warna Tema */}
                                <div>
                                    <InputLabel htmlFor="primary_color" value="Warna Tema Lembaga" />
                                    <div className="mt-1 flex items-center space-x-2">
                                        <input
                                            type="color"
                                            id="primary_color"
                                            value={data.primary_color}
                                            onChange={(e) => setData('primary_color', e.target.value)}
                                            className="h-10 w-12 cursor-pointer rounded border border-gray-300 p-1"
                                        />
                                        <TextInput
                                            type="text"
                                            value={data.primary_color}
                                            onChange={(e) => setData('primary_color', e.target.value)}
                                            className="block w-28 uppercase"
                                            maxLength={7}
                                        />
                                        <div className="flex flex-wrap gap-1">
                                            {PRESET_COLORS.map((color) => (
                                                <button
                                                    key={color.hex}
                                                    type="button"
                                                    title={color.label}
                                                    onClick={() => setData('primary_color', color.hex)}
                                                    className="h-7 w-7 rounded-full border border-white ring-1 ring-gray-200 transition-transform hover:scale-110"
                                                    style={{ backgroundColor: color.hex }}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                    <InputError message={errors.primary_color} className="mt-2" />
                                </div>
                            </div>

                            {/* Alamat Lengkap */}
                            <div>
                                <InputLabel htmlFor="address" value="Alamat Lengkap Lembaga" />
                                <textarea
                                    id="address"
                                    rows="2"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <InputError message={errors.address} className="mt-2" />
                            </div>

                            {/* Logo Lembaga */}
                            <div>
                                <InputLabel htmlFor="logo" value="Logo Lembaga" />
                                <div className="mt-2 flex items-center space-x-4">
                                    {logoPreview ? (
                                        <img
                                            src={logoPreview}
                                            alt="Preview Logo"
                                            className="h-16 w-16 rounded-lg border border-gray-200 object-cover p-1 shadow-sm"
                                        />
                                    ) : (
                                        <div className="flex h-16 w-16 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400">
                                            Belum Ada Logo
                                        </div>
                                    )}
                                    <input
                                        id="logo"
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                        onChange={handleLogoChange}
                                        className="text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                </div>
                                <InputError message={errors.logo} className="mt-2" />
                            </div>

                            {/* Tombol Simpan */}
                            <div className="flex items-center justify-between border-t border-gray-100 pt-6">
                                <div>
                                    {recentlySuccessful && (
                                        <span className="text-sm text-green-600">
                                            Perubahan berhasil disimpan!
                                        </span>
                                    )}
                                </div>
                                <PrimaryButton disabled={processing} className="px-6 py-2.5">
                                    {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
