import { startStimulusApp } from '@symfony/stimulus-bundle';
import TextareaAutogrow from 'stimulus-textarea-autogrow';

const app = startStimulusApp();
app.register('textarea-autogrow', TextareaAutogrow);
