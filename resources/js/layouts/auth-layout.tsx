export default function AuthLayout({ children }: { children: React.ReactNode }) {
    return (
        <div className="">
            <div className="bg-primary-900 relative flex items-center">
                <div
                    className="fixed top-0 left-[200px] z-20 hidden h-screen w-full bg-cover bg-center md:block"
                    style={{ backgroundImage: 'url(/images/bg-2.jpg)' }}
                >
                    <div className="bg-primary-900/60 h-full w-full"></div>
                </div>
                <div className="bg-primary z-30 h-screen w-full max-w-full p-4 shadow-xl md:max-w-1/2 md:bg-white md:p-8">
                    <div className="flex h-full w-full items-center justify-center rounded-lg bg-white">
                        <div className="w-[400px] rounded-lg bg-white md:w-[450px]">{children}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
