# Browser and accessibility verification

Chrome 157.0.8103.40 ran against the local Laravel application with disposable test data. The run passed **37 assertions**.

- Desktop at 1440 × 900. Verified sign-in, invalid credential feedback, sign-out, protected URL redirects, workspace selection and switching, and exclusion of an unrelated workspace. Verified owner navigation and access to workspace settings.
- Tablet at 768 × 1024 and mobile at 390 × 844. Verified no horizontal overflow, keyboard opening of the native modal drawer, initial focus on Close, Tab containment, Escape closing, and focus return to the trigger.
- Keyboard and forms. Verified the first Tab exposes the skip link, the email field has a visible focus outline, and server validation associates the error with the labelled email field. The tests found form fields by their accessible labels.
- Permissions. Verified the sales role does not receive the settings link and receives HTTP 403 for a direct settings URL.
- Motion. Verified the GSAP and ScrollTrigger chunks are skipped under reduced motion and CSS transition duration is zero. With motion enabled, dispatching pagehide cleared the ScrollTrigger instances and the overview entrance's inline transform.
- JavaScript disabled. Verified sign-in, selecting a workspace, opening settings through desktop navigation, and sign-out. The responsive modal drawer uses Alpine and was exercised with animations disabled through the reduced-motion preference and with motion enabled in the browser suite.
- Brand. Chrome loaded the original 1251 × 1251 PNG recovered from the cover of the owner's Official Logo Edition PDF. The supplied artwork has a red mark and white lettering, so it is shown on a near-black surface. The image was extracted without cropping, tracing, recolouring, or changing its proportions.

This was a Chromium run with simulated viewport sizes. Physical devices, other browser engines, and screen-reader testing were outside the run.
