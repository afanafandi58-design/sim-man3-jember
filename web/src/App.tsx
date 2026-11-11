import { useEffect, useState, type ReactNode } from 'react'
import { motion } from 'framer-motion'
import {
  berita,
  kepalaMadrasah,
  nav,
  newsDigital,
  quickLinks,
  quotes,
  site,
  visiMisi,
} from './data/content'

type LinkProps = {
  href: string
  children: ReactNode
  className?: string
  onNavigate?: () => void
}

function External({ href, children, className, onNavigate }: LinkProps) {
  const isExternal = href.startsWith('http')
  return (
    <a
      href={href}
      className={className}
      onClick={onNavigate}
      {...(isExternal ? { target: '_blank', rel: 'noreferrer' } : {})}
    >
      {children}
    </a>
  )
}

export default function App() {
  const [menuOpen, setMenuOpen] = useState(false)
  const [scrolled, setScrolled] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  const closeMenu = () => setMenuOpen(false)

  return (
    <>
      <header
        className="site-header"
        style={{ boxShadow: scrolled ? '0 8px 30px rgba(4,36,30,0.08)' : 'none' }}
      >
        <div className="container header-inner">
          <a className="brand" href="#beranda" aria-label="MAN 3 Jember — beranda">
            <img src="/images/logo.png" alt="" width={44} height={44} />
            <span className="brand-text">
              <span className="brand-name">MAN 3 JEMBER</span>
              <span className="brand-sub">Cerdas Kreatif</span>
            </span>
          </a>

          <nav className="nav-desktop" aria-label="Navigasi utama">
            {nav.map((item) =>
              'children' in item && item.children ? (
                <details key={item.label} className="nav-item">
                  <summary>{item.label}</summary>
                  <div className="nav-dropdown">
                    {item.children.map((child) => (
                      <External key={child.label} href={child.href}>
                        {child.label}
                      </External>
                    ))}
                  </div>
                </details>
              ) : (
                <External key={item.label} href={item.href}>
                  {item.label}
                </External>
              ),
            )}
          </nav>

          <button
            type="button"
            className="menu-toggle"
            aria-expanded={menuOpen}
            aria-controls="nav-mobile"
            onClick={() => setMenuOpen((v) => !v)}
          >
            <span
              className="sr-only"
              style={{
                position: 'absolute',
                width: 1,
                height: 1,
                overflow: 'hidden',
                clip: 'rect(0 0 0 0)',
              }}
            >
              Menu
            </span>
            {menuOpen ? '✕' : '☰'}
          </button>
        </div>

        <nav
          id="nav-mobile"
          className={`container nav-mobile${menuOpen ? ' open' : ''}`}
          aria-label="Navigasi mobile"
        >
          {nav.map((item) =>
            'children' in item && item.children ? (
              <details key={item.label}>
                <summary>{item.label}</summary>
                <div>
                  {item.children.map((child) => (
                    <External key={child.label} href={child.href} onNavigate={closeMenu}>
                      {child.label}
                    </External>
                  ))}
                </div>
              </details>
            ) : (
              <External key={item.label} href={item.href} onNavigate={closeMenu}>
                {item.label}
              </External>
            ),
          )}
        </nav>
      </header>

      <main>
        <section className="hero" id="beranda" aria-label="Beranda MAN 3 Jember">
          <div className="hero-media" aria-hidden="true">
            <img src="/images/hero-campus.jpg" alt="" />
          </div>
          <div className="hero-content">
            <div className="hero-brand">
              <img src="/images/logo.png" alt="" width={72} height={72} />
              <h1 className="hero-brand-name">{site.brand}</h1>
            </div>
            <p className="hero-headline">{site.taglineShort}</p>
            <p className="hero-support">
              {site.taglineCivitas}. {site.motto}
            </p>
            <div className="hero-cta">
              <a className="btn btn-primary" href="#sambutan">
                Sambutan Kepala Madrasah
              </a>
              <a className="btn btn-ghost" href="#berita">
                Berita Terbaru
              </a>
            </div>
          </div>
        </section>

        <section className="section" id="sambutan">
          <div className="container sambutan">
            <motion.div
              className="sambutan-photo"
              initial={{ opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, amount: 0.3 }}
              transition={{ duration: 0.6 }}
            >
              <img src={kepalaMadrasah.photo} alt={`Foto ${kepalaMadrasah.name}`} />
            </motion.div>
            <motion.div
              className="sambutan-body"
              initial={{ opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, amount: 0.3 }}
              transition={{ duration: 0.6, delay: 0.1 }}
            >
              <p className="section-kicker">Editorial</p>
              <h2>{kepalaMadrasah.name}</h2>
              <p className="sambutan-role">{kepalaMadrasah.title}</p>
              <p className="sambutan-date">{kepalaMadrasah.date}</p>
              <p className="sambutan-excerpt">{kepalaMadrasah.excerpt}</p>
              <External className="btn btn-solid" href={kepalaMadrasah.link}>
                Selengkapnya
              </External>
            </motion.div>
          </div>
        </section>

        <section className="section quotes" aria-labelledby="quotes-title">
          <div className="container">
            <div className="section-head">
              <p className="section-kicker">Suara Civitas</p>
              <h2 className="section-title" id="quotes-title">
                Refleksi warga madrasah
              </h2>
              <p className="section-lead">Cuplikan semangat dan nilai yang hidup di MAN 3 Jember.</p>
            </div>
            <div className="quote-track">
              {quotes.map((q, i) => (
                <motion.blockquote
                  key={q.author}
                  className="quote-item"
                  initial={{ opacity: 0, y: 16 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, amount: 0.2 }}
                  transition={{ duration: 0.45, delay: i * 0.06 }}
                >
                  <p>“{q.text}”</p>
                  <cite>{q.author}</cite>
                </motion.blockquote>
              ))}
            </div>
          </div>
        </section>

        <section className="section" id="berita">
          <div className="container">
            <div className="section-head">
              <p className="section-kicker">Berita Terbaru</p>
              <h2 className="section-title">Kabar dari MAN 3 Jember</h2>
              <p className="section-lead">
                Ringkasan berita yang sama seperti di situs resmi — dibuka di man3jember.sch.id.
              </p>
            </div>
            <div className="berita-list">
              {berita.map((item, i) => (
                <motion.a
                  key={item.title}
                  className="berita-item"
                  href={item.link}
                  target="_blank"
                  rel="noreferrer"
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, amount: 0.15 }}
                  transition={{ duration: 0.45, delay: i * 0.05 }}
                >
                  <img src={item.image} alt="" loading="lazy" />
                  <div className="berita-body">
                    <p className="berita-date">{item.date}</p>
                    <h3>{item.title}</h3>
                    <p>{item.excerpt}</p>
                    <span className="more">Baca selengkapnya →</span>
                  </div>
                </motion.a>
              ))}
            </div>
          </div>
        </section>

        <section className="section" id="visi-misi">
          <div className="container visi-block">
            <div>
              <div className="section-head">
                <p className="section-kicker">Visi Misi</p>
                <h2 className="section-title">{visiMisi.title}</h2>
              </div>
              <div className="visi-panel">
                <p>{visiMisi.summary}</p>
                <External className="btn btn-outline" href={visiMisi.link}>
                  Baca Visi Misi lengkap
                </External>
              </div>
            </div>
            <div>
              <div className="section-head">
                <p className="section-kicker">Info Madrasah</p>
                <h2 className="section-title">Akses cepat</h2>
                <p className="section-lead">Tautan menu yang sama dengan situs live.</p>
              </div>
              <div className="links-grid">
                {quickLinks.map((link) => (
                  <External key={link.label} href={link.href}>
                    {link.label}
                  </External>
                ))}
              </div>
            </div>
          </div>
        </section>

        <section className="section" id="news-digital">
          <div className="container">
            <div className="section-head">
              <p className="section-kicker">News Digital</p>
              <h2 className="section-title">Highlight terbitan</h2>
            </div>
            <div className="news-digital">
              {newsDigital.map((n) => (
                <External key={n.label + n.published} href={n.href}>
                  <span>{n.label}</span>
                  <span>Diterbitkan : {n.published}</span>
                </External>
              ))}
            </div>
          </div>
        </section>
      </main>

      <footer className="site-footer">
        <div className="container footer-grid">
          <div className="footer-brand">
            <img src="/images/logo.png" alt="" width={52} height={52} />
            <div>
              <strong>{site.brand}</strong>
              <p>
                {site.taglineShort} · {site.taglineCivitas}
              </p>
            </div>
          </div>
          <div className="footer-col">
            <h3>Kontak</h3>
            <a href={`mailto:${site.email}`}>{site.email}</a>
            <External href={site.liveUrl}>Situs resmi man3jember.sch.id</External>
            <External href="https://prodistik.man3jember.sch.id/">PRODISTIK</External>
          </div>
          <div className="footer-col">
            <h3>Media sosial</h3>
            <External href={site.social.facebook}>Facebook</External>
            <External href={site.social.instagram}>Instagram @mantiger.official</External>
            <External href={site.social.youtube}>YouTube MAN 3 Jember</External>
          </div>
        </div>
        <div className="container footer-bottom">
          <span>Konten mengacu pada situs publik MAN 3 Jember.</span>
          <span>Redesign visual · nuansa hijau dingin</span>
        </div>
      </footer>
    </>
  )
}
