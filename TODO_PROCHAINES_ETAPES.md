# 📋 TODO - Prochaines Étapes d'Implémentation

**Statut**: 🟡 Phase 1 Complétée - Phase 2 À Commencer

---

## 🎯 Phase 2: Intégration des Composants Branding

### Priority 1: Section Bénéfices (4 Piliers)

**Fichier à modifier**: `templates/home/index.html.twig`

**Tâche**: Ajouter section avec 4 bénéfices comme sur les cartes de visite

```html
<!-- À ajouter après la section .stats -->
<section class="benefits-section">
  <div class="container">
    <div class="benefits-grid">
      
      <!-- Pilier 1: Nettoyage à l'eau pure -->
      <div class="benefit-item">
        <div class="benefit-icon">💧</div>
        <h4 class="benefit-title">Nettoyage<br>à l'eau pure</h4>
        <p class="benefit-description">Zéro produit chimique</p>
      </div>

      <!-- Pilier 2: Sécurité maximale -->
      <div class="benefit-item">
        <div class="benefit-icon">✅</div>
        <h4 class="benefit-title">Sécurité<br>maximale</h4>
        <p class="benefit-description">Techniciens formés au travail en hauteur</p>
      </div>

      <!-- Pilier 3: Respect de l'environnement -->
      <div class="benefit-item">
        <div class="benefit-icon">🌿</div>
        <h4 class="benefit-title">Respect de<br>l'environnement</h4>
        <p class="benefit-description">Aucun résidu chimique</p>
      </div>

      <!-- Pilier 4: Performance optimale -->
      <div class="benefit-item">
        <div class="benefit-icon">📈</div>
        <h4 class="benefit-title">Performance<br>optimale</h4>
        <p class="benefit-description">+15% rendement retrouvé</p>
      </div>

    </div>
  </div>
</section>
```

**Estimated time**: 15 minutes

---

### Priority 2: Tagline du Héro

**Fichier à modifier**: `templates/home/index.html.twig`

**Tâche**: Ajouter le tagline dans la section héro

```html
<!-- Dans .hero .container, après le texte de lead -->
<p class="hero-tagline">✨ Un panneau propre, un rendement optimal !</p>
```

**Estimated time**: 5 minutes

---

### Priority 3: Badge Spécialité

**Fichier à modifier**: `templates/home/index.html.twig`

**Tâche**: Ajouter badge avant le titre de la section services

```html
<!-- Avant h2.section-title dans section#services -->
<div class="speciality-badge">Spécialiste du nettoyage</div>
```

**Estimated time**: 5 minutes

---

### Priority 4: Améliorer Register.html.twig

**Fichier à modifier**: `templates/auth/register.html.twig`

**Tâche**: Appliquer même pattern qu'auth/login.html.twig

**Changes**:
- Supprimer Tailwind CSS
- Utiliser `.auth-container` et `.auth-card`
- Ajouter `.auth-form` pour formulaire
- Ajouter `.auth-footer` pour lien login
- Tester responsive

**Estimated time**: 30 minutes

---

## 🎨 Phase 3: Optimisations Visuelles

### Priority 5: Ajouter Contact Info Style

**Fichier à modifier**: `templates/home/index.html.twig` ou footer

**Tâche**: Styliser la section contact avec `.contact-info`

```html
<div class="contact-info">
  <div class="contact-icon">📍</div>
  <div class="contact-details">
    <h4>Adresse</h4>
    <p>Route des dragons 7, 1033 Cheseaux-sur-Lausanne</p>
  </div>
</div>
```

**Estimated time**: 20 minutes

---

### Priority 6: Dividers Doré

**Fichier à modifier**: `templates/home/index.html.twig`

**Tâche**: Ajouter `.divider-gold` entre sections principales

```html
<div class="divider-gold"></div>
```

**Estimated time**: 10 minutes

---

## 🧪 Phase 4: Tests & QA

### Priority 7: Test Responsive Complet

**Checklist**:
- [ ] Desktop 1920px
- [ ] Desktop 1280px
- [ ] Tablet 768px
- [ ] Mobile 640px
- [ ] Mobile 375px
- [ ] Print mode (Ctrl+P)

**Tools**: DevTools Chrome, Firefox

**Estimated time**: 45 minutes

---

### Priority 8: Test Accessibilité

**Checklist**:
- [ ] Navigation au clavier (Tab)
- [ ] Screen reader test (NVDA/JAWS)
- [ ] Focus visible sur tous les éléments
- [ ] Contraste WCAG AA
- [ ] Axe DevTools scan

**Tools**: Axe DevTools, WAVE, NVDA

**Estimated time**: 1 heure

---

### Priority 9: Test Lighthouse

**Checklist**:
- [ ] Performance: >85
- [ ] Accessibility: >95
- [ ] Best Practices: >90
- [ ] SEO: >95

**Tools**: Chrome DevTools → Lighthouse

**Estimated time**: 30 minutes

---

## 📊 Phase 5: Optimisations Performance

### Priority 10: Lazy Loading Images

**Fichier à modifier**: Tous les templates avec images

**Tâche**: Ajouter `loading="lazy"` sur les images

```html
<img src="..." loading="lazy" alt="...">
```

**Impact**: Réduit temps chargement initial

**Estimated time**: 30 minutes

---

### Priority 11: WebP avec Fallback

**Tâche**: Convertir images en WebP avec fallback JPEG

```html
<picture>
  <source srcset="image.webp" type="image/webp">
  <img src="image.jpg" alt="...">
</picture>
```

**Estimated time**: 1 heure

---

### Priority 12: Minification & Bundling

**Tâche**: Vérifier Webpack Encore

```bash
npm run build  # Production build
```

**Checklist**:
- [ ] CSS minifiée
- [ ] JS minifiée
- [ ] Sourcemaps générées
- [ ] Assets hashées

**Estimated time**: 20 minutes

---

## 📱 Phase 6: Mobile Excellence

### Priority 13: PWA Setup

**Tâche**: Créer manifest.json et service worker

**Files**:
- `public/manifest.json` - App metadata
- `public/service-worker.js` - Offline support

**Features**:
- Offline mode
- Add to homescreen
- Push notifications (optionnel)

**Estimated time**: 2 heures

---

### Priority 14: Mobile Menu Animation

**Tâche**: Améliorer hamburger menu avec Stimulus

**Current**: JavaScript vanilla
**Future**: Stimulus controller avec animations

**Estimated time**: 45 minutes

---

## 🎨 Phase 7: Branding Advanced

### Priority 15: Logo Redesign (Optionnel)

**Décision**: Garder logo actuel ou ajouter brosse/balai?

**Options**:
- Option A: Garder minimaliste (4 carrés)
- Option B: Ajouter brosse concept
- Option C: Hybrid modern+concept

**Estimated time**: 2-4 heures

---

### Priority 16: Fonts Optimization

**Tâche**: Subset fonts, variable fonts

**Current**: Google Fonts full
**Optimized**: Subset latin + variable fonts

**Impact**: -30% font size

**Estimated time**: 1 heure

---

## 📄 Phase 8: Documentation & Handoff

### Priority 17: Documentation Technique

**Tâche**: Créer guide pour futurs développeurs

**Contenu**:
- Architecture CSS
- Composants disponibles
- Design system
- Conventions code
- Setup local

**File**: `docs/TECHNICAL_GUIDE.md`

**Estimated time**: 2 heures

---

### Priority 18: Changelog

**Tâche**: Documenter tous les changements

**File**: `CHANGELOG.md`

**Format**:
```markdown
## [2.0] - 2026-08-10
### Added
- CSS modulaire
- Menu mobile
- Accessibilité WCAG AA

### Changed
- Palette de couleurs
- Architecture templates

### Fixed
- Responsive issues
```

**Estimated time**: 30 minutes

---

## 📅 Timeline Estimée

### Semaine 1 (Court Terme)
- [ ] Phase 2: Composants branding (1h30)
- [ ] Phase 3: Optimisations visuelles (1h)
- [ ] Phase 4: Tests complets (2h30)

**Total**: ~5 heures

### Semaine 2-3 (Moyen Terme)
- [ ] Phase 5: Performance (2h)
- [ ] Phase 6: Mobile (3h)
- [ ] Phase 7: Branding avancé (2-4h)

**Total**: ~7-9 heures

### Semaine 4-6 (Long Terme)
- [ ] Phase 8: Documentation (3h)
- [ ] Tests production
- [ ] Déploiement

**Total**: ~3+ heures

---

## 🚀 Deployment Checklist

Avant de déployer en production:

- [ ] Tous les tests passent (responsive, a11y, performance)
- [ ] Lighthouse score >85 sur toutes les métriques
- [ ] Images optimisées (WebP, lazy loading)
- [ ] CSS minifiée et bundlée
- [ ] JS minifiée et bundlée
- [ ] Meta tags SEO vérifiés
- [ ] Robots.txt et sitemap.xml
- [ ] DNS/SSL vérifiés
- [ ] Analytics configuré
- [ ] Monitoring actif

---

## 📞 Questions Fréquentes

### Q: Est-ce que je dois faire tout ça?
**A**: Non, cela dépend de vos priorités:
- Essential: Phase 1 ✅ + Phase 2 (composants branding)
- Recommended: Phase 1-4
- Complete: Phase 1-8

### Q: Combien de temps pour tout?
**A**: ~20-25 heures réparties sur 4-6 semaines

### Q: Puis-je sauter certaines phases?
**A**: Oui, mais recommandé de faire Phase 2 et 4 (tests)

### Q: Qui fait quoi?
**A**: 
- Frontend dev: Phase 2, 3, 4, 5, 6
- Designer: Phase 7 (logo optionnel)
- QA: Phase 4, 8 (tests)
- DevOps: Phase 8 (deployment)

---

## 📚 Ressources

### Documentation Interne
- `IMPROVEMENTS.md` - Détails des améliorations
- `REFONTE_COMPLETE.md` - Rapport technique
- `ALIGNEMENT_CARTES_VISITE.md` - Branding alignment

### Ressources Externes
- [WCAG 2.1](https://www.w3.org/WAI/WCAG21/quickref/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)

---

## ✨ Tips & Tricks

### CSS
- Utiliser variables CSS pour themas
- Media queries = mobile-first
- BEM naming pour clarté

### JavaScript
- Vanilla JS pour petit interactions
- Stimulus pour controllers
- Alpine.js pour directives si besoin

### Performance
- Webp + fallback
- Lazy loading images
- Code splitting
- Minification

### Testing
- Lighthouse régulièrement
- Axe DevTools pour a11y
- Real devices pour mobile

---

## 🎯 Success Criteria

Vous saurez que c'est bon quand:

✅ Tous les breakpoints responsive  
✅ Lighthouse >85 partout  
✅ WCAG AA validé  
✅ Menu hamburger smooth  
✅ Couleurs alignées cartes  
✅ Documentation à jour  
✅ Tests passent  
✅ Deploy en production ✈️  

---

**Last Updated**: 10 août 2026  
**Status**: 🟡 Phase 1 ✅ | Phase 2+ 📋 TODO  
**Next Step**: Commencer Phase 2 Priority 1
