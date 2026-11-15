import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const PRESET_COLORS = [
    { label: 'Biru Utama', hex: '#2563eb' },
    { label: 'Indigo', hex: '#4f46e5' },
    { label: 'Emerald Hijau', hex: '#059669' },
    { label: 'Ungu Edukasi', hex: '#7c3aed' },
    { label: 'Amber Hangat', hex: '#d97706' },
    { label: 'Rose Merah', hex: '#e11d48' },
];

export default function CreateOnboarding({ appHost = 'localhost' }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        slug: '',
        phone: '',
        address: '',
        description: '',
        logo: null,
        primary_color: '#2563eb',
    });

    const [slugManuallyEdited, setSlugManuallyEdited] = useState(false);
    const [logoPreview, setLogoPreview] = useState(null);
    const [subdomainCheck, setSubdomainCheck] = useState({
        checking: false,
        checked: false,
        available: false,
        message: '',
    });

    const generateSlug = (text) => {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const handleNameChange = (e) => {
        const newName = e.target.value;
        setData((prev) => ({
            ...prev,
            name: newName,
            slug: slugManuallyEdited ? prev.slug : generateSlug(newName),
        }));
    };

    const handleSlugChange = (e) => {
        setSlugManuallyEdited(true);
        setData('slug', generateSlug(e.target.value));
    };

    useEffect(() => {
        if (!data.slug || data.slug.length < 3) {
            setSubdomainCheck({ checking: false, checked: false, available: false, message: '' });
            return;
        }

        const timer = setTimeout(() => {
            setSubdomainCheck((prev) => ({ ...prev, checking: true }));
            fetch(route('onboarding.check-subdomain', { slug: data.slug }))
                .then((res) => res.json())
                .then((res) => {
                    setSubdomainCheck({
                        checking: false,
                        checked: true,
                        available: res.available,
                        message: res.message,
                    });
                })
                .catch(() => {
                    setSubdomainCheck({
                        checking: false,
                        checked: false,
                        available: false,
                        message: 'Gagal memeriksa ketersediaan subdomain.',
                    });
                });
        }, 400);

        return () => clearTimeout(timer);
    }, [data.slug]);

    const handleLogoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('logo', file);
            setLogoPreview(URL.createObjectURL(file));
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('onboarding.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Onboarding Lembaga Bimbingan Belajar (LBB)
                </h2>
            }
        >
            <Head title="Buat Lembaga Baru - Onboarding" />

            <div className="py-10">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm sm:p-10">
                        <div className="mb-8 border-b border-gray-100 pb-6">
                            <span className="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                Langkah 1 dari 1: Pengaturan Profil LBB
                            </span>
                            <h1 className="mt-3 text-2xl font-bold tracking-tight text-gray-900">
                                Daftarkan Lembaga Anda
                            </h1>
                            <p className="mt-1 text-sm text-gray-600">
                                Tentukan nama lembaga, subdomain akses eksklusif, serta identitas visual bimbingan belajar Anda.
                            </p>
                        </div>

                        <form onSubmit={submit} className="space-y-6">
                            {/* Nama Lembaga */}
                            <div>
                                <InputLabel htmlFor="name" value="Nama Lembaga Bimbingan Belajar *" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={data.name}
                                    placeholder="Contoh: Bimbel Bintang Prestasi"
                                    className="mt-1 block w-full"
                                    isFocused={true}
                                    onChange={handleNameChange}
                                    required
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            {/* Pilihan Subdomain */}
                            <div>
                                <InputLabel htmlFor="slug" value="Pilih Subdomain Akses Portal *" />
                                <div className="mt-1 flex rounded-md shadow-sm">
                                    <TextInput
                                        id="slug"
                                        type="text"
                                        name="slug"
                                        value={data.slug}
                                        placeholder="bintang-prestasi"
                                        className="block w-full rounded-r-none"
                                        onChange={handleSlugChange}
                                        required
                                    />
                                    <span className="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                                        .{appHost}
                                    </span>
                                </div>
                                <div className="mt-1 flex items-center space-x-2 text-xs">
                                    {subdomainCheck.checking && (
                                        <span className="text-gray-500">Memeriksa ketersediaan...</span>
                                    )}
                                    {!subdomainCheck.checking && subdomainCheck.checked && (
                                        <span
                                            className={
                                                subdomainCheck.available
                                                    ? 'font-medium text-green-600'
                                                    : 'font-medium text-red-600'
                                            }
                                        >
                                            {subdomainCheck.message}
                                        </span>
                                    )}
                                </div>
                                <p className="mt-1 text-xs text-gray-500">
                                    Subdomain ini akan menjadi alamat situs dan portal siswa/tutor Anda, misal: <strong>http://{data.slug || 'nama-lbb'}.{appHost}</strong>
                                </p>
                                <InputError message={errors.slug} className="mt-2" />
                            </div>

                            {/* Deskripsi / Tagline */}
                            <div>
                                <InputLabel htmlFor="description" value="Deskripsi Singkat / Tagline" />
                                <textarea
                                    id="description"
                                    rows="3"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Contoh: Solusi terbaik persiapan SNBT, kedinasan, dan peningkatan prestasi akademik siswa SD, SMP, SMA."
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
                                        placeholder="081234567890"
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
                                    placeholder="Jl. Pendidikan No. 45, Kebayoran Baru, Jakarta Selatan"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <InputError message={errors.address} className="mt-2" />
                            </div>

                            {/* Logo Lembaga */}
                            <div>
                                <InputLabel htmlFor="logo" value="Logo Lembaga (Opsional)" />
                                <div className="mt-2 flex items-center space-x-4">
                                    {logoPreview ? (
                                        <img
                                            src={logoPreview}
                                            alt="Preview Logo"
                                            className="h-16 w-16 rounded-lg border border-gray-200 object-cover p-1 shadow-sm"
                                        />
                                    ) : (
                                        <div className="flex h-16 w-16 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400">
                                            Logo
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
                                <p className="mt-1 text-xs text-gray-500">
                                    Format didukung: PNG, JPG, WebP, SVG. Maksimal 2MB.
                                </p>
                                <InputError message={errors.logo} className="mt-2" />
                            </div>

                            {/* Tombol Simpan */}
                            <div className="flex items-center justify-end border-t border-gray-100 pt-6">
                                <PrimaryButton
                                    className="px-6 py-2.5 text-base"
                                    disabled={processing || (subdomainCheck.checked && !subdomainCheck.available)}
                                >
                                    {processing ? 'Menyiapkan Lembaga...' : 'Selesaikan Onboarding'}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
